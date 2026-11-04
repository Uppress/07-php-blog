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
    page_header('Post not found');
    ?>
    <section class="empty-state not-found">
      <p class="eyebrow">404 · Not found</p>
      <h1>This story isn't here.</h1>
      <p>It may have moved, been removed, or never been published.</p>
      <a class="button" href="<?= e(site_url('index.php')) ?>">Browse latest posts</a>
    </section>
    <?php
    page_footer();
    exit;
}

$wordCount = preg_match_all('/[\p{L}\p{N}]+/u', strip_tags($post['body'])) ?: 0;
$readingMinutes = max(1, (int)ceil($wordCount / 200));
$relatedStmt = db()->prepare(
    "SELECT p.title, p.slug, p.body, p.excerpt, p.cover_image, p.published_at,
            c.name AS category_name, c.slug AS category_slug, a.name AS author_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     JOIN admins a ON a.id = p.author_id
     WHERE p.category_id = ? AND p.status = 'published' AND p.id <> ?
     ORDER BY p.published_at DESC, p.id DESC
     LIMIT 3"
);
$relatedStmt->execute([$post['category_id'], $post['id']]);
$relatedPosts = $relatedStmt->fetchAll();

page_header($post['title']);
?>
<article class="article">
  <header class="article-header">
    <a class="eyebrow" href="<?= e(category_url($post['category_slug'])) ?>"><?= e($post['category_name']) ?></a>
    <h1><?= e($post['title']) ?></h1>
    <p class="article-meta">By <?= e($post['author_name']) ?> <span aria-hidden="true">·</span>
      <time datetime="<?= e(date('Y-m-d', strtotime($post['published_at']))) ?>"><?= e(date('F j, Y', strtotime($post['published_at']))) ?></time>
      <span aria-hidden="true">·</span> <?= $readingMinutes ?> min read
    </p>
  </header>
  <?php if ($post['cover_image']): ?>
    <img class="article-cover" src="<?= e(site_url('uploads/' . rawurlencode($post['cover_image']))) ?>" alt="">
  <?php endif; ?>
  <div class="article-body"><?php render_post_body($post['body']); ?></div>
</article>

<?php if ($relatedPosts): ?>
  <section class="related-section" aria-labelledby="related-title">
    <div class="section-heading">
      <p class="eyebrow">Keep exploring</p>
      <h2 id="related-title">Related stories</h2>
    </div>
    <div class="post-grid">
      <?php foreach ($relatedPosts as $relatedPost): render_post_card($relatedPost); endforeach; ?>
    </div>
  </section>
<?php endif; ?>
<?php page_footer(); ?>
