<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';
require_admin();

$categories = db()->query(
    'SELECT c.id, c.name, c.slug, c.description, COUNT(p.id) AS post_count
     FROM categories c LEFT JOIN posts p ON p.category_id = c.id
     GROUP BY c.id ORDER BY c.name'
)->fetchAll();

admin_header('Categories');
if (isset($_GET['error']) && $_GET['error'] === 'in-use'): ?><div class="alert alert-error">A category with posts cannot be deleted. Move or delete its posts first.</div><?php endif; ?>
<div class="panel">
  <div class="panel-heading"><p>Organize posts into categories.</p><a class="button button-small" href="<?= e(site_url('admin/categories/create.php')) ?>">New category</a></div>
  <?php if (!$categories): ?><p>No categories yet.</p><?php else: ?>
    <div class="table-wrap"><table><thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($categories as $category): ?><tr>
        <td><?= e($category['name']) ?></td><td><?= e($category['slug']) ?></td><td><?= (int)$category['post_count'] ?></td>
        <td class="actions"><a href="<?= e(site_url('admin/categories/edit.php?id=' . (int)$category['id'])) ?>">Edit</a>
          <form method="post" action="<?= e(site_url('admin/categories/delete.php')) ?>" onsubmit="return confirm('Delete this category?')">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$category['id'] ?>"><button class="link-button danger-text" type="submit" <?= (int)$category['post_count'] > 0 ? 'disabled title="This category has posts"' : '' ?>>Delete</button>
          </form>
        </td>
      </tr><?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>
<?php admin_footer(); ?>
