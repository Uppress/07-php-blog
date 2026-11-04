<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';
require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = db()->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$id ?: 0]);
$post = $stmt->fetch();
if (!$post) {
    http_response_code(404);
    exit('Post not found.');
}
$categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];
$values = ['title' => $post['title'], 'slug' => $post['slug'], 'category_id' => (string)$post['category_id'], 'body' => $post['body'], 'status' => $post['status']];

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
    $slugCheck = db()->prepare('SELECT COUNT(*) FROM posts WHERE slug = ? AND id <> ?');
    $slugCheck->execute([$values['slug'], $id]);
    if ((int)$slugCheck->fetchColumn() > 0) $errors[] = 'That slug is already in use.';

    $cover = $post['cover_image'];
    if (!$errors && isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $cover = save_cover_image($_FILES['cover_image']);
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }
    if (!$errors) {
        $publishedAt = $values['status'] === 'published'
            ? ($post['published_at'] ?: date('Y-m-d H:i:s'))
            : null;
        try {
            $update = db()->prepare('UPDATE posts SET category_id = ?, title = ?, slug = ?, body = ?, status = ?, cover_image = ?, published_at = ? WHERE id = ?');
            $update->execute([(int)$values['category_id'], $values['title'], $values['slug'], $values['body'], $values['status'], $cover, $publishedAt, $id]);
        } catch (Throwable $exception) {
            if ($cover !== $post['cover_image']) {
                remove_cover_image($cover);
            }
            throw $exception;
        }
        if ($post['cover_image'] !== $cover) {
            remove_cover_image($post['cover_image']);
        }
        redirect(site_url('admin/posts/index.php'));
    }
}

admin_header('Edit post');
if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php require __DIR__ . '/form.php'; admin_footer();
