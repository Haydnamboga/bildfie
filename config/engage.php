<?php
/**
 * Provider engagements (Hire/Invite + Request quote) and reviews.
 */
require_once __DIR__ . '/db.php';

function engage_uuid(): string {
    $d = random_bytes(16); $d[6] = chr(ord($d[6]) & 0x0f | 0x40); $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}

/** Create an invite or quote engagement for a provider. Returns the new id. */
function engage_create(string $type, int $providerId, ?int $fromUserId, ?string $fromName, array $d): int {
    $type = $type === 'quote' ? 'quote' : 'invite';
    return db_insert(
        "INSERT INTO provider_engagements (public_id,provider_id,project_id,from_user_id,from_name,type,subject,message,location,budget,needed_by)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)",
        [engage_uuid(), $providerId, ($d['project_id'] ?? null), $fromUserId, $fromName, $type,
         $d['subject'] ?? null, $d['message'] ?? null, $d['location'] ?? null, $d['budget'] ?? null, $d['needed_by'] ?? null]
    );
}

/** Upsert one review per reviewer per provider, then recompute the provider's rating. */
function review_create(int $providerId, ?int $reviewerId, ?string $reviewerName, int $rating, ?string $project, ?string $body): void {
    $rating = max(1, min(5, $rating));
    db_stmt(
        "INSERT INTO provider_reviews (public_id,provider_id,user_id,reviewer_name,rating,project,body,status)
         VALUES (?,?,?,?,?,?,?,'published')
         ON DUPLICATE KEY UPDATE rating=VALUES(rating), project=VALUES(project), body=VALUES(body)",
        [engage_uuid(), $providerId, $reviewerId, ($reviewerName ?: 'Anonymous'), $rating, $project, $body]
    );
    review_recompute($providerId);
}

/** Recompute providers.rating + reviews_count from published reviews. */
function review_recompute(int $providerId): void {
    db_stmt(
        "UPDATE providers SET
            rating = COALESCE((SELECT ROUND(AVG(rating),1) FROM provider_reviews WHERE provider_id=? AND status='published'), 0),
            reviews_count = (SELECT COUNT(*) FROM provider_reviews WHERE provider_id=? AND status='published')
         WHERE id=?",
        [$providerId, $providerId, $providerId]
    );
}

/** Published reviews for a provider profile. */
function provider_reviews(int $providerId, int $limit = 20): array {
    $limit = max(1, min(100, $limit));
    return db_all("SELECT * FROM provider_reviews WHERE provider_id=? AND status='published' ORDER BY created_at DESC LIMIT $limit", [$providerId]);
}

/** All engagements (invites/quotes) received by a member's provider listing. */
function provider_engagements_for_user(int $userId): array {
    return db_all(
        "SELECT e.*, p.name AS provider_name
         FROM provider_engagements e JOIN providers p ON p.id = e.provider_id
         WHERE p.user_id = ? ORDER BY e.created_at DESC", [$userId]
    );
}
