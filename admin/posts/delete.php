<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$stmt = db()->prepare('SELECT cover_image FROM posts WHERE id = ?');
$stmt->execute([$id ?: 0]);
$post = $stmt->fetch();
if ($post) {
    db()->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);
    remove_cover_image($post['cover_image']);
}
redirect(site_url('admin/posts/index.php'));
