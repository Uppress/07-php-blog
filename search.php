<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$query = trim((string)($_GET['q'] ?? ''));
$posts = [];
if ($query !== '') {
    $stmt = db()->prepare(
        "SELECT p.title, p.slug, p.body, p.cover_image, p.published_at, c.name AS category_name, c.slug AS category_slug, a.name AS author_name,
                MATCH(p.title, p.body) AGAINST (? IN NATURAL LANGUAGE MODE) AS relevance
         FROM posts p JOIN categories c ON c.id = p.category_id JOIN admins a ON a.id = p.author_id
         WHERE p.status = 'published' AND MATCH(p.title, p.body) AGAINST (? IN NATURAL LANGUAGE MODE)
         ORDER BY relevance DESC, p.published_at DESC
         LIMIT 50"
    );
    $stmt->execute([$query, $query]);
    $posts = $stmt->fetchAll();
}

page_header('Search');
?>
<section class="page-intro"><p class="eyebrow">Discover</p><h1>Search posts</h1>
  <form class="search-form" method="get" action="<?= e(site_url('search.php')) ?>">
    <label class="sr-only" for="search-query">Search posts</label>
    <input id="search-query" type="search" name="q" value="<?= e($query) ?>" placeholder="What are you looking for?" required>
    <button type="submit">Search</button>
  </form>
</section>
<?php if ($query !== ''): ?>
  <p class="results-label"><?= count($posts) ?> result<?= count($posts) === 1 ? '' : 's' ?> for “<?= e($query) ?>”</p>
  <section class="post-grid">
    <?php if (!$posts): ?><div class="empty-state"><h2>No matching posts</h2><p>Try a different search term.</p></div><?php endif; ?>
    <?php foreach ($posts as $post): ?>
      <article class="post-card">
        <?php if ($post['cover_image']): ?><a href="<?= e(site_url('post.php?slug=' . rawurlencode($post['slug']))) ?>"><img class="post-cover" src="<?= e(site_url('uploads/' . rawurlencode($post['cover_image']))) ?>" alt=""></a><?php endif; ?>
        <div class="post-card-content"><a class="eyebrow" href="<?= e(site_url('category.php?slug=' . rawurlencode($post['category_slug']))) ?>"><?= e($post['category_name']) ?></a>
        <h2><a href="<?= e(site_url('post.php?slug=' . rawurlencode($post['slug']))) ?>"><?= e($post['title']) ?></a></h2><p><?= e(excerpt($post['body'])) ?></p>
        <div class="post-meta"><?= e($post['author_name']) ?> · <?= e(date('M j, Y', strtotime($post['published_at']))) ?></div></div>
      </article>
    <?php endforeach; ?>
  </section>
<?php endif; ?>
<?php page_footer(); ?>
