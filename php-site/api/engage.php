<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/engage.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok' => false, 'error' => 'Method not allowed.']); exit; }
if (!is_logged_in()) { echo json_encode(['ok' => false, 'error' => 'Please sign in to continue.', 'login' => true]); exit; }

$u    = current_user();
$type = $_POST['type'] ?? '';
$pid  = (int)($_POST['provider_id'] ?? 0);
$prov = $pid ? db_one("SELECT id, user_id, name FROM providers WHERE id=? AND status<>'suspended' LIMIT 1", [$pid]) : null;

if (!$prov)                                       { echo json_encode(['ok' => false, 'error' => 'This professional is not available right now.']); exit; }
if ((int)$prov['user_id'] === (int)$u['id'])      { echo json_encode(['ok' => false, 'error' => "That's your own profile — you can't do this here."]); exit; }

$msg = trim($_POST['message'] ?? '');

// validate the linked project (must belong to the requester)
$projectId = (int) ($_POST['project_id'] ?? 0);
$projName  = '';
if ($projectId) {
    $pr = db_one("SELECT id,name FROM projects WHERE id=? AND owner_user_id=? LIMIT 1", [$projectId, (int)$u['id']]);
    if ($pr) $projName = $pr['name']; else $projectId = 0;
}

switch ($type) {
    case 'invite':
        $role = trim($_POST['role'] ?? '');
        $subject = trim(($role ?: 'Invitation') . ($projName ? ' · ' . $projName : ''));
        engage_create('invite', $pid, (int)$u['id'], $u['name'], ['subject' => $subject, 'message' => $msg, 'project_id' => $projectId ?: null]);
        echo json_encode(['ok' => true, 'message' => 'Invite sent to ' . $prov['name'] . ($projName ? ' for ' . $projName : '') . '.']);
        break;

    case 'quote':
        if ($msg === '') { echo json_encode(['ok' => false, 'error' => 'Tell the professional what you need a price for.']); exit; }
        engage_create('quote', $pid, (int)$u['id'], $u['name'], [
            'message'   => $msg,
            'location'  => trim($_POST['location'] ?? '') ?: null,
            'budget'    => trim($_POST['budget'] ?? '') ?: null,
            'needed_by' => trim($_POST['needed_by'] ?? '') ?: null,
            'project_id'=> $projectId ?: null,
        ]);
        echo json_encode(['ok' => true, 'message' => 'Quote request sent.']);
        break;

    case 'review':
        $rating = (int)($_POST['rating'] ?? 0);
        if ($rating < 1)   { echo json_encode(['ok' => false, 'error' => 'Please pick a star rating.']); exit; }
        if ($msg === '')   { echo json_encode(['ok' => false, 'error' => 'Please write a few words about your experience.']); exit; }
        review_create($pid, (int)$u['id'], $u['name'], $rating, trim($_POST['project'] ?? '') ?: null, $msg);
        echo json_encode(['ok' => true, 'message' => 'Thanks for your review!']);
        break;

    default:
        echo json_encode(['ok' => false, 'error' => 'Unknown action.']);
}
