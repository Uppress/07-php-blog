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
    ?>
    <section class="empty-state not-found">
      <p class="eyebrow">404 · Not found</p>
      <h1>Category not found.</h1>
      <p>This category may have moved or been removed.</p>
      <a class="button" href="<?= e(site_url('index.php')) ?>">Browse latest posts</a>
    </section>
    <?php
    page_footer();
    exit;
}

$postsStmt = db()->prepare(
    "SELECT p.title, p.slug, p.body, p.excerpt, p.cover_image, p.published_at,
            c.name AS category_name, c.slug AS category_slug, a.name AS author_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     JOIN admins a ON a.id = p.author_id
     WHERE p.category_id = ? AND p.status = 'published'
     ORDER BY p.published_at DESC, p.id DESC"
);
$postsStmt->execute([$category['id']]);
$posts = $postsStmt->fetchAll();

page_header($category['name']);
?>
<section class="page-intro category-intro">
  <p class="eyebrow">Browse category</p>
  <h1><?= e($category['name']) ?></h1>
  <?php if ($category['description']): ?><p><?= e($category['description']) ?></p><?php endif; ?>
</section>
<section class="post-grid" aria-label="<?= e($category['name']) ?> posts">
  <?php if (!$posts): ?><div class="empty-state"><h2>No posts in this category yet</h2><p>Check back soon for new stories.</p></div><?php endif; ?>
  <?php foreach ($posts as $post): render_post_card($post); endforeach; ?>
</section>
<?php page_footer(); ?>
