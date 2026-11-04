<?php
<form class="panel form-panel" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="field field-wide"><label for="title">Title</label><input id="title" name="title" maxlength="240" required value="<?= e($values['title']) ?>"></div>
    <div class="field field-wide"><label for="slug">Slug</label><input id="slug" name="slug" maxlength="260" value="<?= e($values['slug']) ?>" placeholder="generated-from-title"></div>
    <div class="field"><label for="category_id">Category</label><select id="category_id" name="category_id" required><option value="">Select category</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (string)$category['id'] === $values['category_id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="draft" <?= $values['status'] === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= $values['status'] === 'published' ? 'selected' : '' ?>>Published</option></select></div>
    <div class="field field-wide"><label for="body">Content</label><textarea id="body" name="body" rows="16" required><?= e($values['body']) ?></textarea></div>
    <div class="field field-wide"><label for="cover_image">Cover image <span class="hint">(JPEG, PNG, GIF, or WebP; up to 5 MB)</span></label><input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp"><?php if (isset($post['cover_image']) && $post['cover_image']): ?><p class="hint">Current image: <?= e($post['cover_image']) ?></p><?php endif; ?></div>
  </div>
  <div class="form-actions"><button class="button" type="submit">Save post</button><a class="button button-muted" href="<?= e(site_url('admin/posts/index.php')) ?>">Cancel</a></div>
</form>
