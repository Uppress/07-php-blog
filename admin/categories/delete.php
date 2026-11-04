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
$stmt = db()->prepare('SELECT COUNT(*) FROM posts WHERE category_id = ?');
$stmt->execute([$id ?: 0]);
if ((int)$stmt->fetchColumn() > 0) {
    redirect(site_url('admin/categories/index.php?error=in-use'));
}
db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id ?: 0]);
redirect(site_url('admin/categories/index.php'));
