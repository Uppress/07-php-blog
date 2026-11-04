<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
require_admin();

$counts = db()->query(
    "SELECT
        (SELECT COUNT(*) FROM posts) AS total_posts,
        (SELECT COUNT(*) FROM posts WHERE status = 'published') AS published_posts,
        (SELECT COUNT(*) FROM posts WHERE status = 'draft') AS draft_posts,
        (SELECT COUNT(*) FROM categories) AS total_categories"
)->fetch();

$latestPosts = db()->query(
    'SELECT p.id, p.title, p.status, p.updated_at, c.name AS category_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     ORDER BY p.updated_at DESC, p.id DESC
     LIMIT 5'
)->fetchAll();

$postsPerCategory = db()->query(
    'SELECT c.id, c.name, COUNT(p.id) AS post_count
     FROM categories c
     LEFT JOIN posts p ON p.category_id = c.id
     GROUP BY c.id, c.name
     ORDER BY c.name'
)->fetchAll();

admin_header('Dashboard');
?>
<div class="stats-grid">
  <div class="stat-card"><span>All posts</span><strong><?= (int)$counts['total_posts'] ?></strong></div>
  <div class="stat-card"><span>Published</span><strong><?= (int)$counts['published_posts'] ?></strong></div>
  <div class="stat-card"><span>Drafts</span><strong><?= (int)$counts['draft_posts'] ?></strong></div>
  <div class="stat-card"><span>Categories</span><strong><?= (int)$counts['total_categories'] ?></strong></div>
</div>
<section class="panel">
  <div class="panel-heading"><h2>Latest posts</h2><a class="button button-small" href="<?= e(site_url('admin/posts/create.php')) ?>">New post</a></div>
  <?php if (!$latestPosts): ?><p>No posts yet. Create your first post to get started.</p><?php else: ?>
    <div class="table-wrap"><table><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Updated</th><th></th></tr></thead><tbody>
      <?php foreach ($latestPosts as $post): ?><tr>
        <td><?= e($post['title']) ?></td>
        <td><?= e($post['category_name']) ?></td>
        <td><span class="status status-<?= e($post['status']) ?>"><?= e(ucfirst($post['status'])) ?></span></td>
        <td><?= e(date('M j, Y', strtotime($post['updated_at']))) ?></td>
        <td><a href="<?= e(site_url('admin/posts/edit.php?id=' . (int)$post['id'])) ?>">Edit</a></td>
      </tr><?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</section>
<section class="panel">
  <div class="panel-heading"><h2>Posts per category</h2><a href="<?= e(site_url('admin/categories/index.php')) ?>">Manage categories</a></div>
  <?php if (!$postsPerCategory): ?><p>No categories yet.</p><?php else: ?>
    <div class="table-wrap"><table><thead><tr><th>Category</th><th>Posts</th></tr></thead><tbody>
      <?php foreach ($postsPerCategory as $category): ?><tr>
        <td><?= e($category['name']) ?></td><td><?= (int)$category['post_count'] ?></td>
      </tr><?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</section>
<?php admin_footer(); ?>
