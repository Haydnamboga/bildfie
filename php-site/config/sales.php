<?php
/**
 * Transaction core — data access for sales documents, inventory and contracts.
 * Documents (invoices / estimates / proposals / credit notes) share one engine.
 * Every query is scoped to the owning user_id so members only ever see their own.
 */
require_once __DIR__ . '/db.php';

/** RFC-4122 v4 UUID (matches the project convention used elsewhere). */
function bf_uuid(): string {
    $d = random_bytes(16);
    $d[6] = chr(ord($d[6]) & 0x0f | 0x40);
    $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}

/** Run a write statement; return affected rows. */
function sales_exec(string $sql, array $params = []): int {
    $st  = db_stmt($sql, $params);
    $aff = $st->affected_rows;
    $st->close();
    return $aff;
}

/** Trim a value; empty string becomes null (so DATE/email columns store NULL). */
function sales_nn($v): ?string {
    $v = trim((string) $v);
    return $v === '' ? null : $v;
}

// ──────────────────────────────────────────────────────────────
//  DOCUMENTS  (invoice · estimate · proposal · credit_note)
// ──────────────────────────────────────────────────────────────

function doc_type_norm(string $t): string {
    $t = str_replace('-', '_', strtolower(trim($t)));
    return in_array($t, ['invoice','estimate','proposal','credit_note'], true) ? $t : 'invoice';
}

function doc_prefix(string $type): string {
    return ['invoice'=>'INV','estimate'=>'EST','proposal'=>'PRP','credit_note'=>'CRN'][$type] ?? 'DOC';
}

function doc_label(string $type): string {
    return ['invoice'=>'Invoice','estimate'=>'Estimate','proposal'=>'Proposal','credit_note'=>'Credit Note'][$type] ?? 'Document';
}

/** Back-to-list URL for a document type. */
function doc_list_url(string $type): string {
    return [
        'invoice'     => '/pages/dashboard/invoices.php',
        'estimate'    => '/pages/sales/estimates.php',
        'proposal'    => '/pages/sales/proposals.php',
        'credit_note' => '/pages/sales/credit-notes.php',
    ][$type] ?? '/pages/dashboard/index.php';
}

/** Sidebar active key for a document type. */
function doc_sp(string $type): string {
    return ['invoice'=>'invoices','estimate'=>'estimates','proposal'=>'proposals','credit_note'=>'credit-notes'][$type] ?? 'invoices';
}

/** Valid statuses for a document type (used by the create/edit form). */
function doc_statuses(string $type): array {
    return [
        'invoice'     => ['draft','sent','paid','overdue','void'],
        'estimate'    => ['draft','sent','accepted','declined','expired','invoiced'],
        'proposal'    => ['draft','sent','open','accepted','declined','expired'],
        'credit_note' => ['draft','issued','applied','void'],
    ][$type] ?? ['draft','sent'];
}

/** Next per-user, per-type, per-year document number, e.g. INV-2026-001. */
function doc_next_number(int $uid, string $type): string {
    $year = date('Y');
    $n = (int) db_value(
        "SELECT COUNT(*) FROM documents WHERE user_id=? AND doc_type=? AND YEAR(created_at)=?",
        [$uid, $type, $year]
    );
    return doc_prefix($type) . '-' . $year . '-' . str_pad((string)($n + 1), 3, '0', STR_PAD_LEFT);
}

/**
 * Create or update a document together with its line items (one transaction).
 * $items is a list of ['description','quantity','unit_price'].
 * Returns the document id.
 */
function doc_save(int $uid, string $type, array $d, array $items, ?int $id = null): int {
    $type = doc_type_norm($type);

    $subtotal = 0.0;
    $clean = [];
    foreach ($items as $it) {
        $desc = trim((string)($it['description'] ?? ''));
        if ($desc === '') continue;
        $qty   = (float)($it['quantity']   ?? 1);
        $price = (float)($it['unit_price'] ?? 0);
        $line  = round($qty * $price, 2);
        $subtotal += $line;
        $clean[] = [$desc, $qty, $price, $line];
    }
    $taxRate   = (float)($d['tax_rate'] ?? 0);
    $taxAmount = round($subtotal * $taxRate / 100, 2);
    $total     = round($subtotal + $taxAmount, 2);
    $status    = sales_nn($d['status'] ?? '') ?: 'draft';
    $currency  = sales_nn($d['currency'] ?? '') ?: 'KES';
    $projectId = (($d['project_id'] ?? '') !== '' && (int)$d['project_id'] > 0) ? (int)$d['project_id'] : null;
    $clientId  = (($d['client_id']  ?? '') !== '' && (int)$d['client_id']  > 0) ? (int)$d['client_id']  : null;

    $c = db();
    $c->begin_transaction();
    try {
        if ($id) {
            if (!db_value("SELECT id FROM documents WHERE id=? AND user_id=?", [$id, $uid])) {
                throw new RuntimeException('Document not found');
            }
            sales_exec(
                "UPDATE documents SET client_name=?, client_id=?, client_email=?, client_phone=?, subject=?, project_id=?,
                   issue_date=?, due_date=?, currency=?, subtotal=?, tax_rate=?, tax_amount=?,
                   total=?, notes=?, status=? WHERE id=? AND user_id=?",
                [sales_nn($d['client_name'] ?? '') ?: 'Client', $clientId, sales_nn($d['client_email'] ?? ''),
                 sales_nn($d['client_phone'] ?? ''), sales_nn($d['subject'] ?? ''), $projectId,
                 sales_nn($d['issue_date'] ?? ''), sales_nn($d['due_date'] ?? ''), $currency,
                 $subtotal, $taxRate, $taxAmount, $total, sales_nn($d['notes'] ?? ''), $status, $id, $uid]
            );
            sales_exec("DELETE FROM document_items WHERE document_id=?", [$id]);
        } else {
            $number = doc_next_number($uid, $type);
            $id = db_insert(
                "INSERT INTO documents
                   (public_id,user_id,doc_type,number,client_name,client_id,client_email,client_phone,subject,project_id,
                    issue_date,due_date,currency,subtotal,tax_rate,tax_amount,total,notes,status)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [bf_uuid(), $uid, $type, $number, sales_nn($d['client_name'] ?? '') ?: 'Client', $clientId,
                 sales_nn($d['client_email'] ?? ''), sales_nn($d['client_phone'] ?? ''),
                 sales_nn($d['subject'] ?? ''), $projectId, sales_nn($d['issue_date'] ?? ''), sales_nn($d['due_date'] ?? ''),
                 $currency, $subtotal, $taxRate, $taxAmount, $total, sales_nn($d['notes'] ?? ''), $status]
            );
        }
        $so = 0;
        foreach ($clean as [$desc, $qty, $price, $line]) {
            db_insert(
                "INSERT INTO document_items (document_id,description,quantity,unit_price,line_total,sort_order)
                 VALUES (?,?,?,?,?,?)",
                [$id, $desc, $qty, $price, $line, $so++]
            );
        }
        $c->commit();
    } catch (Throwable $e) {
        $c->rollback();
        throw $e;
    }
    return $id;
}

function doc_all(int $uid, string $type, string $q = '', string $status = ''): array {
    $sql = "SELECT * FROM documents WHERE user_id=? AND doc_type=?";
    $p = [$uid, doc_type_norm($type)];
    if ($q !== '')      { $sql .= " AND (number LIKE ? OR client_name LIKE ? OR subject LIKE ?)"; $l = "%$q%"; array_push($p, $l, $l, $l); }
    if ($status !== '') { $sql .= " AND status=?"; $p[] = $status; }
    $sql .= " ORDER BY created_at DESC, id DESC";
    return db_all($sql, $p);
}

function doc_get(int $uid, int $id): ?array {
    return db_one("SELECT * FROM documents WHERE id=? AND user_id=?", [$id, $uid]);
}

function doc_items(int $docId): array {
    return db_all("SELECT * FROM document_items WHERE document_id=? ORDER BY sort_order, id", [$docId]);
}

function doc_set_status(int $uid, int $id, string $status): bool {
    return sales_exec("UPDATE documents SET status=? WHERE id=? AND user_id=?", [$status, $id, $uid]) >= 0
        && (bool) doc_get($uid, $id);
}

function doc_delete(int $uid, int $id): bool {
    return sales_exec("DELETE FROM documents WHERE id=? AND user_id=?", [$id, $uid]) > 0;
}

/** Status a source document takes once it has been converted onward. */
function doc_converted_status(string $srcType): string {
    return $srcType === 'estimate' ? 'invoiced' : ($srcType === 'proposal' ? 'accepted' : 'sent');
}

/** Clone a document into another type (estimate→invoice, proposal→estimate). Returns new id or null. */
function doc_convert(int $uid, int $id, string $toType): ?int {
    $src = doc_get($uid, $id);
    if (!$src) return null;
    $toType = doc_type_norm($toType);
    $items  = array_map(
        fn($it) => ['description'=>$it['description'], 'quantity'=>$it['quantity'], 'unit_price'=>$it['unit_price']],
        doc_items($id)
    );
    $newId = doc_save($uid, $toType, [
        'client_name'  => $src['client_name'],  'client_email' => $src['client_email'],
        'client_phone' => $src['client_phone'], 'subject'      => $src['subject'],
        'issue_date'   => date('Y-m-d'),         'due_date'     => null,
        'currency'     => $src['currency'],      'tax_rate'     => $src['tax_rate'],
        'notes'        => $src['notes'],         'status'       => 'draft',
    ], $items);
    sales_exec("UPDATE documents SET status=?, converted_to_id=? WHERE id=? AND user_id=?",
        [doc_converted_status($src['doc_type']), $newId, $id, $uid]);
    return $newId;
}

/** KPI aggregates for a document type. */
function doc_stats(int $uid, string $type): array {
    $row = db_one(
        "SELECT
            COUNT(*) AS cnt,
            COALESCE(SUM(total),0) AS total_value,
            COALESCE(SUM(CASE WHEN status='paid' THEN total ELSE 0 END),0) AS paid_value,
            COALESCE(SUM(CASE WHEN status IN ('sent','overdue','open') THEN total ELSE 0 END),0) AS outstanding_value,
            COALESCE(SUM(CASE WHEN status='overdue' THEN total ELSE 0 END),0) AS overdue_value,
            COALESCE(SUM(CASE WHEN status IN ('sent','open') THEN 1 ELSE 0 END),0) AS open_cnt,
            COALESCE(SUM(CASE WHEN status IN ('accepted','paid','invoiced','applied') THEN 1 ELSE 0 END),0) AS won_cnt,
            COALESCE(SUM(CASE WHEN status IN ('sent','open','accepted') THEN total ELSE 0 END),0) AS pipeline_value
         FROM documents WHERE user_id=? AND doc_type=?",
        [$uid, doc_type_norm($type)]
    );
    return $row ?: ['cnt'=>0,'total_value'=>0,'paid_value'=>0,'outstanding_value'=>0,'overdue_value'=>0,'open_cnt'=>0,'won_cnt'=>0,'pipeline_value'=>0];
}

// ──────────────────────────────────────────────────────────────
//  CLIENTS  +  project linking
// ──────────────────────────────────────────────────────────────

/** The current user's projects (for the document "linked project" picker). */
function doc_user_projects(int $uid): array {
    return db_all("SELECT id, name FROM projects WHERE owner_user_id=? ORDER BY created_at DESC, name", [$uid]);
}

function client_all(int $uid, string $q = ''): array {
    $sql = "SELECT * FROM clients WHERE user_id=?"; $p = [$uid];
    if ($q !== '') { $sql .= " AND (name LIKE ? OR company LIKE ? OR email LIKE ?)"; $l = "%$q%"; array_push($p, $l, $l, $l); }
    $sql .= " ORDER BY name";
    return db_all($sql, $p);
}

function client_get(int $uid, int $id): ?array {
    return db_one("SELECT * FROM clients WHERE id=? AND user_id=?", [$id, $uid]);
}

function client_save(int $uid, array $d, ?int $id = null): int {
    $name = sales_nn($d['name'] ?? '') ?: 'Client';
    if ($id) {
        sales_exec("UPDATE clients SET name=?,email=?,phone=?,company=?,notes=? WHERE id=? AND user_id=?",
            [$name, sales_nn($d['email'] ?? ''), sales_nn($d['phone'] ?? ''), sales_nn($d['company'] ?? ''), sales_nn($d['notes'] ?? ''), $id, $uid]);
        return $id;
    }
    return db_insert("INSERT INTO clients (public_id,user_id,name,email,phone,company,notes) VALUES (?,?,?,?,?,?,?)",
        [bf_uuid(), $uid, $name, sales_nn($d['email'] ?? ''), sales_nn($d['phone'] ?? ''), sales_nn($d['company'] ?? ''), sales_nn($d['notes'] ?? '')]);
}

/** Return the id of a client with this name (creating it if new). Null for an empty name. */
function client_find_or_create(int $uid, string $name, ?string $email = null, ?string $phone = null): ?int {
    $name = trim($name);
    if ($name === '') return null;
    $row = db_one("SELECT id, email, phone FROM clients WHERE user_id=? AND name=? LIMIT 1", [$uid, $name]);
    if ($row) {
        if (($email && !$row['email']) || ($phone && !$row['phone'])) {
            sales_exec("UPDATE clients SET email=COALESCE(NULLIF(email,''),?), phone=COALESCE(NULLIF(phone,''),?) WHERE id=?",
                [sales_nn($email ?? ''), sales_nn($phone ?? ''), (int)$row['id']]);
        }
        return (int) $row['id'];
    }
    return client_save($uid, ['name' => $name, 'email' => $email, 'phone' => $phone]);
}

function client_delete(int $uid, int $id): bool {
    sales_exec("UPDATE documents SET client_id=NULL WHERE client_id=? AND user_id=?", [$id, $uid]);
    return sales_exec("DELETE FROM clients WHERE id=? AND user_id=?", [$id, $uid]) > 0;
}

// ──────────────────────────────────────────────────────────────
//  INVENTORY
// ──────────────────────────────────────────────────────────────

function inv_stock_status(int $qty, int $reorder): string {
    if ($qty <= 0) return 'out';
    if ($reorder > 0 && $qty <= $reorder) return 'low';
    return 'in';
}

function inv_all(int $uid, string $q = '', string $type = ''): array {
    $sql = "SELECT * FROM inventory_items WHERE user_id=?"; $p = [$uid];
    if ($q !== '')    { $sql .= " AND (name LIKE ? OR sku LIKE ?)"; $l = "%$q%"; array_push($p, $l, $l); }
    if ($type !== '') { $sql .= " AND item_type=?"; $p[] = $type; }
    $sql .= " ORDER BY name";
    return db_all($sql, $p);
}

function inv_get(int $uid, int $id): ?array {
    return db_one("SELECT * FROM inventory_items WHERE id=? AND user_id=?", [$id, $uid]);
}

function inv_save(int $uid, array $d, ?int $id = null): int {
    $name    = sales_nn($d['name'] ?? '') ?: 'Untitled item';
    $type    = in_array($d['item_type'] ?? '', ['material','hardware','software','service'], true) ? $d['item_type'] : 'material';
    $price   = (float)($d['unit_price'] ?? 0);
    $qty     = (int)($d['quantity'] ?? 0);
    $reorder = (int)($d['reorder_level'] ?? 0);
    $sku     = sales_nn($d['sku'] ?? '');
    $unit    = sales_nn($d['unit'] ?? '');
    $loc     = sales_nn($d['location'] ?? '');
    if ($id) {
        sales_exec(
            "UPDATE inventory_items SET name=?,sku=?,item_type=?,unit_price=?,quantity=?,unit=?,reorder_level=?,location=?
             WHERE id=? AND user_id=?",
            [$name, $sku, $type, $price, $qty, $unit, $reorder, $loc, $id, $uid]
        );
        return $id;
    }
    return db_insert(
        "INSERT INTO inventory_items (public_id,user_id,name,sku,item_type,unit_price,quantity,unit,reorder_level,location)
         VALUES (?,?,?,?,?,?,?,?,?,?)",
        [bf_uuid(), $uid, $name, $sku, $type, $price, $qty, $unit, $reorder, $loc]
    );
}

function inv_delete(int $uid, int $id): bool {
    return sales_exec("DELETE FROM inventory_items WHERE id=? AND user_id=?", [$id, $uid]) > 0;
}

function inv_stats(int $uid): array {
    $row = db_one(
        "SELECT COUNT(*) AS cnt,
            COALESCE(SUM(CASE WHEN quantity<=0 THEN 1 ELSE 0 END),0) AS out_cnt,
            COALESCE(SUM(CASE WHEN quantity>0 AND reorder_level>0 AND quantity<=reorder_level THEN 1 ELSE 0 END),0) AS low_cnt,
            COALESCE(SUM(unit_price*quantity),0) AS stock_value
         FROM inventory_items WHERE user_id=?",
        [$uid]
    );
    return $row ?: ['cnt'=>0,'out_cnt'=>0,'low_cnt'=>0,'stock_value'=>0];
}

// ──────────────────────────────────────────────────────────────
//  CONTRACTS
// ──────────────────────────────────────────────────────────────

function contract_next_number(int $uid): string {
    $year = date('Y');
    $n = (int) db_value("SELECT COUNT(*) FROM contracts WHERE user_id=? AND YEAR(created_at)=?", [$uid, $year]);
    return 'CON-' . $year . '-' . str_pad((string)($n + 1), 3, '0', STR_PAD_LEFT);
}

function contract_all(int $uid, string $q = '', string $status = ''): array {
    $sql = "SELECT * FROM contracts WHERE user_id=?"; $p = [$uid];
    if ($q !== '')      { $sql .= " AND (number LIKE ? OR title LIKE ? OR counterparty LIKE ?)"; $l = "%$q%"; array_push($p, $l, $l, $l); }
    if ($status !== '') { $sql .= " AND status=?"; $p[] = $status; }
    $sql .= " ORDER BY created_at DESC, id DESC";
    return db_all($sql, $p);
}

function contract_get(int $uid, int $id): ?array {
    return db_one("SELECT * FROM contracts WHERE id=? AND user_id=?", [$id, $uid]);
}

function contract_save(int $uid, array $d, ?int $id = null): int {
    $title        = sales_nn($d['title'] ?? '') ?: 'Untitled contract';
    $counterparty = sales_nn($d['counterparty'] ?? '') ?: 'Counterparty';
    $value        = ($d['value'] ?? '') === '' ? null : (float) str_replace([',', ' '], '', (string)$d['value']);
    $status       = sales_nn($d['status'] ?? '') ?: 'draft';
    if ($id) {
        sales_exec(
            "UPDATE contracts SET title=?,counterparty=?,value=?,currency=?,start_date=?,end_date=?,body=?,status=?
             WHERE id=? AND user_id=?",
            [$title, $counterparty, $value, sales_nn($d['currency'] ?? '') ?: 'KES',
             sales_nn($d['start_date'] ?? ''), sales_nn($d['end_date'] ?? ''), sales_nn($d['body'] ?? ''),
             $status, $id, $uid]
        );
        return $id;
    }
    return db_insert(
        "INSERT INTO contracts (public_id,user_id,number,title,counterparty,value,currency,start_date,end_date,body,status)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)",
        [bf_uuid(), $uid, contract_next_number($uid), $title, $counterparty, $value,
         sales_nn($d['currency'] ?? '') ?: 'KES', sales_nn($d['start_date'] ?? ''), sales_nn($d['end_date'] ?? ''),
         sales_nn($d['body'] ?? ''), $status]
    );
}

function contract_set_status(int $uid, int $id, string $status): bool {
    $signed = $status === 'signed' ? date('Y-m-d H:i:s') : null;
    return sales_exec("UPDATE contracts SET status=?, signed_at=COALESCE(?, signed_at) WHERE id=? AND user_id=?",
        [$status, $signed, $id, $uid]) >= 0;
}

function contract_delete(int $uid, int $id): bool {
    return sales_exec("DELETE FROM contracts WHERE id=? AND user_id=?", [$id, $uid]) > 0;
}

function contract_stats(int $uid): array {
    $row = db_one(
        "SELECT COUNT(*) AS cnt,
            COALESCE(SUM(CASE WHEN status IN ('signed','active') THEN 1 ELSE 0 END),0) AS active_cnt,
            COALESCE(SUM(CASE WHEN status IN ('draft','sent') THEN 1 ELSE 0 END),0) AS pending_cnt,
            COALESCE(SUM(CASE WHEN status IN ('signed','active') THEN value ELSE 0 END),0) AS active_value
         FROM contracts WHERE user_id=?",
        [$uid]
    );
    return $row ?: ['cnt'=>0,'active_cnt'=>0,'pending_cnt'=>0,'active_value'=>0];
}
