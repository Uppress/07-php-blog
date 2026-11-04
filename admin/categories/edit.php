<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';
require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = db()->prepare('SELECT * FROM categories WHERE id = ?');
$stmt->execute([$id ?: 0]);
$category = $stmt->fetch();
if (!$category) {
    http_response_code(404);
    exit('Category not found.');
}
$values = ['name' => $category['name'], 'slug' => $category['slug'], 'description' => $category['description'] ?? ''];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $slugInput = trim((string)($_POST['slug'] ?? ''));
    if ($slugInput === '') {
        $slugInput = (string)($_POST['name'] ?? '');
    }
    $values = [
        'name' => trim((string)($_POST['name'] ?? '')),
        'slug' => slugify($slugInput),
        'description' => trim((string)($_POST['description'] ?? '')),
    ];
    if ($values['name'] === '' || mb_strlen($values['name'], 'UTF-8') > 120) $errors[] = 'Name is required and must be 120 characters or fewer.';
    if ($values['slug'] === '' || mb_strlen($values['slug'], 'UTF-8') > 160) $errors[] = 'Slug is required and must be 160 characters or fewer.';
    $check = db()->prepare('SELECT COUNT(*) FROM categories WHERE slug = ? AND id <> ?');
    $check->execute([$values['slug'], $id]);
    if ((int)$check->fetchColumn() > 0) $errors[] = 'That slug is already in use.';
    if (!$errors) {
        $update = db()->prepare('UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?');
        $update->execute([$values['name'], $values['slug'], $values['description'] ?: null, $id]);
        redirect(site_url('admin/categories/index.php'));
    }
}

admin_header('Edit category');
if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php require __DIR__ . '/form.php'; admin_footer();
