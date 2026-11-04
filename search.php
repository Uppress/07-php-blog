<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$query = trim((string)($_GET['q'] ?? ''));
$posts = [];
if ($query !== '') {
    $select = "SELECT p.title, p.slug, p.body, p.excerpt, p.cover_image, p.published_at,
                      c.name AS category_name, c.slug AS category_slug, a.name AS author_name";
    $joins = ' FROM posts p JOIN categories c ON c.id = p.category_id JOIN admins a ON a.id = p.author_id ';
    if (mb_strlen($query, 'UTF-8') < 3) {
        $like = '%' . $query . '%';
        $stmt = db()->prepare(
            $select . $joins .
            "WHERE p.status = 'published' AND (p.title LIKE ? OR p.body LIKE ?)
             ORDER BY p.published_at DESC, p.id DESC LIMIT 50"
        );
        $stmt->execute([$like, $like]);
    } else {
        $stmt = db()->prepare(
            $select . ', MATCH(p.title, p.body) AGAINST (? IN NATURAL LANGUAGE MODE) AS relevance ' . $joins .
            "WHERE p.status = 'published' AND MATCH(p.title, p.body) AGAINST (? IN NATURAL LANGUAGE MODE)
             ORDER BY relevance DESC, p.published_at DESC, p.id DESC LIMIT 50"
        );
        $stmt->execute([$query, $query]);
    }
    $posts = $stmt->fetchAll();
}

page_header('Search');
?>
<section class="page-intro search-intro">
  <p class="eyebrow">Find something interesting</p>
  <h1>Search stories</h1>
  <form class="search-form" method="get" action="<?= e(site_url('search.php')) ?>">
    <label class="sr-only" for="search-query">Search posts</label>
    <input id="search-query" type="search" name="q" value="<?= e($query) ?>" placeholder="What are you looking for?" required>
    <button type="submit">Search</button>
  </form>
</section>
<?php if ($query !== ''): ?>
  <p class="results-label"><?= count($posts) ?> result<?= count($posts) === 1 ? '' : 's' ?> for “<?= e($query) ?>”</p>
  <section class="post-grid" aria-label="Search results">
    <?php if (!$posts): ?><div class="empty-state"><h2>No matching posts</h2><p>Try a different search term.</p></div><?php endif; ?>
    <?php foreach ($posts as $post): render_post_card($post); endforeach; ?>
  </section>
<?php endif; ?>
<?php page_footer(); ?>
