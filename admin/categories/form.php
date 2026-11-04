<form class="panel form-panel" method="post">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="field field-wide"><label for="name">Name</label><input id="name" name="name" maxlength="120" required value="<?= e($values['name']) ?>"></div>
    <div class="field field-wide"><label for="slug">Slug</label><input id="slug" name="slug" maxlength="160" value="<?= e($values['slug']) ?>" placeholder="generated-from-name"></div>
    <div class="field field-wide"><label for="description">Description</label><textarea id="description" name="description" rows="5"><?= e($values['description']) ?></textarea></div>
  </div>
  <div class="form-actions"><button class="button" type="submit">Save category</button><a class="button button-muted" href="<?= e(site_url('admin/categories/index.php')) ?>">Cancel</a></div>
</form>
