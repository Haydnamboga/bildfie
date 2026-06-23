<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/sales.php';
require_login();
$uid = (int) current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /pages/dashboard/index.php'); exit; }

$id  = (int)($_POST['id'] ?? 0);
$doc = doc_get($uid, $id);
if (!$doc) { header('Location: /pages/dashboard/index.php'); exit; }
$type = $doc['doc_type'];

switch ($_POST['action'] ?? '') {
    case 'status':
        $val = $_POST['value'] ?? 'draft';
        if (in_array($val, doc_statuses($type), true)) {
            doc_set_status($uid, $id, $val);
            $_SESSION['doc_flash'] = ['ok', doc_label($type) . ' marked as ' . $val . '.'];
        }
        header('Location: /pages/sales/document-view.php?id=' . $id); exit;

    case 'convert':
        $to = ['estimate'=>'invoice','proposal'=>'estimate'][$type] ?? null;
        if ($to) {
            $newId = doc_convert($uid, $id, $to);
            if ($newId) {
                $_SESSION['doc_flash'] = ['ok', 'Created ' . doc_label($to) . ' from ' . $doc['number'] . '.'];
                header('Location: /pages/sales/document-view.php?id=' . $newId); exit;
            }
        }
        header('Location: /pages/sales/document-view.php?id=' . $id); exit;

    case 'delete':
        doc_delete($uid, $id);
        $_SESSION['doc_flash'] = ['ok', $doc['number'] . ' deleted.'];
        header('Location: ' . doc_list_url($type)); exit;
}

header('Location: /pages/sales/document-view.php?id=' . $id); exit;
