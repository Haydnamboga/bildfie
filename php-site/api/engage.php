<?php
/**
 * POST /api/engage.php
 * JSON API for provider engagement (quote, hire/invite) and reviews.
 * Requires X-Requested-With: fetch header.
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/engage.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Only accept fetch (AJAX) requests
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'fetch') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Direct access not allowed.']);
    exit;
}

// Must be logged in
if (!is_logged_in()) {
    echo json_encode(['ok' => false, 'login' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST required.']);
    exit;
}

$u      = current_user();
$userId = (int) $u['id'];
$action = trim($_POST['action'] ?? '');

// Resolve provider_id (numeric or public_id)
$providerKey = trim($_POST['provider_id'] ?? '');
if ($providerKey === '') {
    echo json_encode(['ok' => false, 'error' => 'provider_id is required.']);
    exit;
}

$provider = is_numeric($providerKey)
    ? db_one("SELECT * FROM providers WHERE id=? LIMIT 1", [(int)$providerKey])
    : db_one("SELECT * FROM providers WHERE public_id=? LIMIT 1", [$providerKey]);

if (!$provider) {
    echo json_encode(['ok' => false, 'error' => 'Provider not found.']);
    exit;
}
$providerId = (int) $provider['id'];

// Cannot engage with yourself
if ((int) $provider['user_id'] === $userId) {
    echo json_encode(['ok' => false, 'error' => 'You cannot send an engagement to yourself.']);
    exit;
}

try {
    if ($action === 'quote') {
        $message = trim($_POST['message'] ?? '');
        if ($message === '') {
            echo json_encode(['ok' => false, 'error' => 'Message is required.']);
            exit;
        }
        engage_create('quote', $providerId, $userId, $u['name'], [
            'subject'    => trim($_POST['subject']    ?? ''),
            'message'    => $message,
            'location'   => trim($_POST['location']   ?? ''),
            'budget'     => trim($_POST['budget']     ?? '') ?: null,
            'needed_by'  => trim($_POST['needed_by']  ?? '') ?: null,
            'project_id' => null,
        ]);
        echo json_encode(['ok' => true]);

    } elseif ($action === 'hire' || $action === 'invite') {
        $message = trim($_POST['message'] ?? '');
        if ($message === '') {
            echo json_encode(['ok' => false, 'error' => 'Message is required.']);
            exit;
        }
        engage_create('invite', $providerId, $userId, $u['name'], [
            'subject'    => trim($_POST['subject']    ?? 'Hire invitation'),
            'message'    => $message,
            'location'   => trim($_POST['location']   ?? ''),
            'budget'     => trim($_POST['budget']     ?? '') ?: null,
            'needed_by'  => null,
            'project_id' => null,
        ]);
        echo json_encode(['ok' => true]);

    } elseif ($action === 'review') {
        $rating = (int) ($_POST['rating'] ?? 0);
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['ok' => false, 'error' => 'Rating must be between 1 and 5.']);
            exit;
        }
        review_create(
            $providerId,
            $userId,
            $u['name'],
            $rating,
            trim($_POST['project'] ?? '') ?: null,
            trim($_POST['body']    ?? '') ?: null
        );
        echo json_encode(['ok' => true]);

    } elseif ($action === 'engage') {
        // Generic engage — alias for invite
        engage_create('invite', $providerId, $userId, $u['name'], [
            'subject'    => trim($_POST['subject']    ?? ''),
            'message'    => trim($_POST['message']    ?? ''),
            'location'   => trim($_POST['location']   ?? ''),
            'budget'     => trim($_POST['budget']     ?? '') ?: null,
            'needed_by'  => trim($_POST['needed_by']  ?? '') ?: null,
            'project_id' => null,
        ]);
        echo json_encode(['ok' => true]);

    } else {
        echo json_encode(['ok' => false, 'error' => 'Unknown action: ' . htmlspecialchars($action)]);
    }
} catch (Throwable $e) {
    error_log('[engage.php] ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Server error. Please try again.']);
}
