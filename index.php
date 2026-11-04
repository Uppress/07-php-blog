<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$posts = db()->query(
    "SELECT p.id, p.title, p.slug, p.body, p.cover_image, p.published_at, c.name AS category_name, c.slug AS category_slug, a.name AS author_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     JOIN admins a ON a.id = p.author_id
     WHERE p.status = 'published'
     ORDER BY p.published_at DESC, p.id DESC
     LIMIT 20"
)->fetchAll();
$categories = db()->query('SELECT name, slug FROM categories ORDER BY name')->fetchAll();

page_header('Latest posts');
?>
<section class="hero">
  <p class="eyebrow">Ideas, stories, and updates</p>
  <h1>Welcome to SWD Blog</h1>
  <p>Explore the latest posts from our team.</p>
  <form class="search-form" method="get" action="<?= e(site_url('search.php')) ?>">
    <label class="sr-only" for="home-search">Search posts</label>
    <input id="home-search" type="search" name="q" placeholder="Search posts..." required>
    <button type="submit">Search</button>
  </form>
</section>
<?php if ($categories): ?>
  <div class="category-pills">
    <?php foreach ($categories as $category): ?>
      <a class="pill" href="<?= e(site_url('category.php?slug=' . rawurlencode($category['slug']))) ?>"><?= e($category['name']) ?></a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<section class="post-grid" aria-label="Latest posts">
  <?php if (!$posts): ?>
    <div class="empty-state"><h2>No published posts yet</h2><p>Check back soon for new stories.</p></div>
  <?php endif; ?>
  <?php foreach ($posts as $post): ?>
    <article class="post-card">
      <?php if ($post['cover_image']): ?>
        <a href="<?= e(site_url('post.php?slug=' . rawurlencode($post['slug']))) ?>"><img class="post-cover" src="<?= e(site_url('uploads/' . rawurlencode($post['cover_image']))) ?>" alt=""></a>
      <?php endif; ?>
      <div class="post-card-content">
        <a class="eyebrow" href="<?= e(site_url('category.php?slug=' . rawurlencode($post['category_slug']))) ?>"><?= e($post['category_name']) ?></a>
        <h2><a href="<?= e(site_url('post.php?slug=' . rawurlencode($post['slug']))) ?>"><?= e($post['title']) ?></a></h2>
        <p><?= e(excerpt($post['body'])) ?></p>
        <div class="post-meta"><?= e($post['author_name']) ?> · <?= e(date('M j, Y', strtotime($post['published_at']))) ?></div>
      </div>
    </article>
  <?php endforeach; ?>
</section>
<?php page_footer(); ?>
