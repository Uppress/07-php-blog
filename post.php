<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$stmt = db()->prepare(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug, a.name AS author_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     JOIN admins a ON a.id = p.author_id
     WHERE p.slug = ? AND p.status = 'published'
     LIMIT 1"
);
$stmt->execute([$slug]);
$post = $stmt->fetch();
if (!$post) {
    http_response_code(404);
}

page_header($post ? $post['title'] : 'Post not found');
if (!$post): ?>
  <section class="empty-state"><h1>Post not found</h1><p>This post may have been removed or is not published.</p><a class="button" href="<?= e(site_url('index.php')) ?>">Back to home</a></section>
<?php else: ?>
  <article class="article">
    <a class="eyebrow" href="<?= e(site_url('category.php?slug=' . rawurlencode($post['category_slug']))) ?>"><?= e($post['category_name']) ?></a>
    <h1><?= e($post['title']) ?></h1>
    <p class="post-meta">By <?= e($post['author_name']) ?> · <?= e(date('F j, Y', strtotime($post['published_at']))) ?></p>
    <?php if ($post['cover_image']): ?><img class="article-cover" src="<?= e(site_url('uploads/' . rawurlencode($post['cover_image']))) ?>" alt=""><?php endif; ?>
    <div class="article-body"><?= nl2br(e($post['body'])) ?></div>
  </article>
<?php endif;
page_footer();
