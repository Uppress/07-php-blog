<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';
$admin = require_admin();
$categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];
$values = ['title' => '', 'slug' => '', 'category_id' => '', 'body' => '', 'status' => 'draft'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $slugInput = trim((string)($_POST['slug'] ?? ''));
    if ($slugInput === '') {
        $slugInput = (string)($_POST['title'] ?? '');
    }
    $values = [
        'title' => trim((string)($_POST['title'] ?? '')),
        'slug' => slugify($slugInput),
        'category_id' => (string)($_POST['category_id'] ?? ''),
        'body' => trim((string)($_POST['body'] ?? '')),
        'status' => (string)($_POST['status'] ?? 'draft'),
    ];
    if ($values['title'] === '' || mb_strlen($values['title'], 'UTF-8') > 240) $errors[] = 'Title is required and must be 240 characters or fewer.';
    if ($values['slug'] === '' || mb_strlen($values['slug'], 'UTF-8') > 260) $errors[] = 'Slug is required and must be 260 characters or fewer.';
    if ($values['body'] === '') $errors[] = 'Post content is required.';
    if (!in_array($values['status'], ['draft', 'published'], true)) $errors[] = 'Choose a valid post status.';
    $categoryCheck = db()->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
    $categoryCheck->execute([$values['category_id']]);
    if ((int)$categoryCheck->fetchColumn() !== 1) $errors[] = 'Choose a valid category.';
    $slugCheck = db()->prepare('SELECT COUNT(*) FROM posts WHERE slug = ?');
    $slugCheck->execute([$values['slug']]);
    if ((int)$slugCheck->fetchColumn() > 0) $errors[] = 'That slug is already in use.';

    $cover = null;
    if (!$errors && isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $cover = save_cover_image($_FILES['cover_image']);
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }

    if (!$errors) {
        $publishedAt = $values['status'] === 'published' ? date('Y-m-d H:i:s') : null;
        try {
            $stmt = db()->prepare('INSERT INTO posts (category_id, author_id, title, slug, body, status, cover_image, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([(int)$values['category_id'], $admin['id'], $values['title'], $values['slug'], $values['body'], $values['status'], $cover, $publishedAt]);
        } catch (Throwable $exception) {
            remove_cover_image($cover);
            throw $exception;
        }
        redirect(site_url('admin/posts/index.php'));
    }
}

admin_header('Create post');
if (!$categories): ?><div class="alert alert-error">Create a category before adding a post. <a href="<?= e(site_url('admin/categories/create.php')) ?>">Create category</a></div><?php endif;
if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php require __DIR__ . '/form.php'; admin_footer();
