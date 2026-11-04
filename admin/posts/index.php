<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';
require_admin();

$status = (string)($_GET['status'] ?? '');
$status = in_array($status, ['draft', 'published'], true) ? $status : '';
$sql = 'SELECT p.id, p.title, p.status, p.updated_at, c.name AS category_name, a.name AS author_name
     FROM posts p JOIN categories c ON c.id = p.category_id JOIN admins a ON a.id = p.author_id
     ' . ($status !== '' ? 'WHERE p.status = ? ' : '') . 'ORDER BY p.updated_at DESC';
$stmt = db()->prepare($sql);
$stmt->execute($status !== '' ? [$status] : []);
$posts = $stmt->fetchAll();

admin_header('Posts');
?>
<div class="panel">
  <div class="panel-heading"><p>Manage your blog posts.</p><a class="button button-small" href="<?= e(site_url('admin/posts/create.php')) ?>">New post</a></div>
  <form class="filter-form" method="get" action="<?= e(site_url('admin/posts/index.php')) ?>">
    <label for="status-filter">Filter by status</label>
    <select id="status-filter" name="status">
      <option value="" <?= $status === '' ? 'selected' : '' ?>>All statuses</option>
      <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
      <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
    </select>
    <button class="button button-small" type="submit">Apply</button>
  </form>
  <?php if (!$posts): ?><p>No posts yet.</p><?php else: ?>
    <div class="table-wrap"><table><thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($posts as $post): ?><tr>
        <td><?= e($post['title']) ?></td><td><?= e($post['category_name']) ?></td><td><?= e($post['author_name']) ?></td>
        <td><span class="status status-<?= e($post['status']) ?>"><?= e(ucfirst($post['status'])) ?></span></td>
        <td><?= e(date('M j, Y', strtotime($post['updated_at']))) ?></td>
        <td class="actions"><a href="<?= e(site_url('admin/posts/edit.php?id=' . (int)$post['id'])) ?>">Edit</a>
          <form method="post" action="<?= e(site_url('admin/posts/delete.php')) ?>" onsubmit="return confirm('Delete this post?')">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$post['id'] ?>"><button class="link-button danger-text" type="submit">Delete</button>
          </form>
        </td>
      </tr><?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>
<?php admin_footer(); ?>
