<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';
require_admin();

$values = ['name' => '', 'slug' => '', 'description' => ''];
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
    $check = db()->prepare('SELECT COUNT(*) FROM categories WHERE slug = ?');
    $check->execute([$values['slug']]);
    if ((int)$check->fetchColumn() > 0) $errors[] = 'That slug is already in use.';
    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)');
        $stmt->execute([$values['name'], $values['slug'], $values['description'] ?: null]);
        redirect(site_url('admin/categories/index.php'));
    }
}

admin_header('Create category');
if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php require __DIR__ . '/form.php'; admin_footer();
