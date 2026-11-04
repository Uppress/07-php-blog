<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$perPage = 6;
$totalPosts = (int)db()->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$totalPages = max(1, (int)ceil($totalPosts / $perPage));
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = max(1, min($page ?: 1, $totalPages));
$offset = ($page - 1) * $perPage;

$stmt = db()->prepare(
    "SELECT p.id, p.title, p.slug, p.body, p.excerpt, p.cover_image, p.published_at,
            c.name AS category_name, c.slug AS category_slug, a.name AS author_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     JOIN admins a ON a.id = p.author_id
     WHERE p.status = 'published'
     ORDER BY p.published_at DESC, p.id DESC
     LIMIT ? OFFSET ?"
);
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();
$categories = db()->query('SELECT name, slug FROM categories ORDER BY name')->fetchAll();

page_header('Latest posts');
?>
<section class="hero">
  <p class="eyebrow">Ideas, stories, and updates</p>
  <h1>Stories worth your time.</h1>
  <p>Thoughtful reads, practical guides, and the latest from our team.</p>
  <form class="search-form" method="get" action="<?= e(site_url('search.php')) ?>">
    <label class="sr-only" for="home-search">Search posts</label>
    <input id="home-search" type="search" name="q" placeholder="Search posts..." required>
    <button type="submit">Search</button>
  </form>
</section>
<?php if ($categories): ?>
  <nav class="category-pills" aria-label="Browse categories">
    <?php foreach ($categories as $category): ?>
      <a class="pill" href="<?= e(category_url($category['slug'])) ?>"><?= e($category['name']) ?></a>
    <?php endforeach; ?>
  </nav>
<?php endif; ?>
<section class="post-grid" aria-label="Latest posts">
  <?php if (!$posts): ?>
    <div class="empty-state"><h2>No published posts yet</h2><p>Check back soon for new stories.</p></div>
  <?php endif; ?>
  <?php foreach ($posts as $post): render_post_card($post); endforeach; ?>
</section>
<?php render_pagination($page, $totalPages, site_url('index.php')); ?>
<?php page_footer(); ?>
