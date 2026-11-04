<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
require_admin();

$counts = [
    'posts' => (int)db()->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
    'published' => (int)db()->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn(),
    'categories' => (int)db()->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
];
$recent = db()->query(
    'SELECT p.id, p.title, p.slug, p.status, p.updated_at, c.name AS category_name
     FROM posts p JOIN categories c ON c.id = p.category_id
     ORDER BY p.updated_at DESC LIMIT 8'
)->fetchAll();

admin_header('Dashboard');
?>
<div class="stats-grid">
  <div class="stat-card"><span>All posts</span><strong><?= $counts['posts'] ?></strong></div>
  <div class="stat-card"><span>Published</span><strong><?= $counts['published'] ?></strong></div>
  <div class="stat-card"><span>Categories</span><strong><?= $counts['categories'] ?></strong></div>
</div>
<section class="panel">
  <div class="panel-heading"><h2>Recently updated</h2><a class="button button-small" href="<?= e(site_url('admin/posts/create.php')) ?>">New post</a></div>
  <?php if (!$recent): ?><p>No posts yet. Create your first post to get started.</p><?php else: ?>
    <div class="table-wrap"><table><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Updated</th><th></th></tr></thead><tbody>
      <?php foreach ($recent as $post): ?><tr>
        <td><?= e($post['title']) ?></td><td><?= e($post['category_name']) ?></td><td><span class="status status-<?= e($post['status']) ?>"><?= e(ucfirst($post['status'])) ?></span></td>
        <td><?= e(date('M j, Y', strtotime($post['updated_at']))) ?></td><td><a href="<?= e(site_url('admin/posts/edit.php?id=' . (int)$post['id'])) ?>">Edit</a></td>
      </tr><?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</section>
<?php admin_footer(); ?>
