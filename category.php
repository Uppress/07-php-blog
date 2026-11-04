<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$stmt = db()->prepare('SELECT id, name, description FROM categories WHERE slug = ? LIMIT 1');
$stmt->execute([$slug]);
$category = $stmt->fetch();
if (!$category) {
    http_response_code(404);
    page_header('Category not found');
    ?><section class="empty-state"><h1>Category not found</h1><a class="button" href="<?= e(site_url('index.php')) ?>">Back to home</a></section><?php
    page_footer();
    exit;
}

$stmt = db()->prepare(
    "SELECT p.title, p.slug, p.body, p.cover_image, p.published_at, a.name AS author_name
     FROM posts p JOIN admins a ON a.id = p.author_id
     WHERE p.category_id = ? AND p.status = 'published'
     ORDER BY p.published_at DESC, p.id DESC"
);
$stmt->execute([$category['id']]);
$posts = $stmt->fetchAll();

page_header($category['name']);
?>
<section class="page-intro"><p class="eyebrow">Category</p><h1><?= e($category['name']) ?></h1><?php if ($category['description']): ?><p><?= e($category['description']) ?></p><?php endif; ?></section>
<section class="post-grid">
  <?php if (!$posts): ?><div class="empty-state"><h2>No posts in this category yet</h2></div><?php endif; ?>
  <?php foreach ($posts as $post): ?>
    <article class="post-card">
      <?php if ($post['cover_image']): ?><a href="<?= e(site_url('post.php?slug=' . rawurlencode($post['slug']))) ?>"><img class="post-cover" src="<?= e(site_url('uploads/' . rawurlencode($post['cover_image']))) ?>" alt=""></a><?php endif; ?>
      <div class="post-card-content"><h2><a href="<?= e(site_url('post.php?slug=' . rawurlencode($post['slug']))) ?>"><?= e($post['title']) ?></a></h2>
      <p><?= e(excerpt($post['body'])) ?></p><div class="post-meta"><?= e($post['author_name']) ?> · <?= e(date('M j, Y', strtotime($post['published_at']))) ?></div></div>
    </article>
  <?php endforeach; ?>
</section>
<?php page_footer(); ?>
