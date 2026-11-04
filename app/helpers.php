<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function site_url(string $path = ''): string
{
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    if (str_ends_with($base, '/admin/posts') || str_ends_with($base, '/admin/categories')) {
        $base = dirname(dirname($base));
    } elseif (str_ends_with($base, '/admin')) {
        $base = dirname($base);
    }
    return ($base === '/' ? '' : $base) . '/' . ltrim($path, '/');
}

function slugify(string $title): string
{
    $text = trim(mb_strtolower($title, 'UTF-8'));
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($text)) ?? '';
    return trim($slug, '-') ?: 'item';
}

function unique_post_slug(PDO $pdo, string $title, ?int $excludeId = null): string
{
    $base = slugify($title);
    $candidate = $base;
    $suffix = 2;
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM posts WHERE slug = ?' . ($excludeId === null ? '' : ' AND id <> ?')
    );

    while (true) {
        $params = $excludeId === null ? [$candidate] : [$candidate, $excludeId];
        $stmt->execute($params);
        if ((int)$stmt->fetchColumn() === 0) {
            return $candidate;
        }
        $candidate = $base . '-' . $suffix++;
    }
}

function excerpt(string $text, int $length = 180): string
{
    $plain = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
    if (mb_strlen($plain, 'UTF-8') <= $length) {
        return $plain;
    }
    return rtrim(mb_substr($plain, 0, $length, 'UTF-8')) . '…';
}

function save_cover_image(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The cover image could not be uploaded.');
    }
    if (!isset($file['tmp_name'], $file['size']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('The cover image upload is invalid.');
    }
    if ($file['size'] > 2 * 1024 * 1024 || $file['size'] <= 0) {
        throw new RuntimeException('Cover images must be no larger than 2 MB.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Use a valid JPEG, PNG, or WebP image.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    $directory = dirname(__DIR__) . '/uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The upload directory is unavailable.');
    }
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $name)) {
        throw new RuntimeException('The cover image could not be saved.');
    }
    return $name;
}

function remove_cover_image(?string $filename): void
{
    if ($filename === null || $filename === '') {
        return;
    }
    $path = dirname(__DIR__) . '/uploads/' . basename($filename);
    if (is_file($path) && !unlink($path)) {
        throw new RuntimeException('The old cover image could not be removed.');
    }
}

function page_header(string $title): void
{
    ?><!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= e($title) ?> · SWD Blog</title>
      <link rel="stylesheet" href="<?= e(site_url('assets/style.css')) ?>">
    </head>
    <body>
    <header class="site-header">
      <a class="brand" href="<?= e(site_url('index.php')) ?>">SWD Blog</a>
      <nav aria-label="Main navigation">
        <a href="<?= e(site_url('index.php')) ?>">Home</a>
        <a href="<?= e(site_url('search.php')) ?>">Search</a>
        <a href="<?= e(site_url('admin/login.php')) ?>">Admin</a>
      </nav>
    </header>
    <main class="container">
    <?php
}

function page_footer(): void
{
    ?></main><footer class="site-footer">SWD Blog</footer></body></html><?php
}

function admin_header(string $title): void
{
    $admin = current_admin();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= e($title) ?> · Blog admin</title>
      <link rel="stylesheet" href="<?= e(site_url('assets/style.css')) ?>">
    </head>
    <body class="admin-body">
      <aside class="sidebar">
        <a class="brand" href="<?= e(site_url('admin/index.php')) ?>">SWD Admin</a>
        <nav aria-label="Admin navigation">
          <a href="<?= e(site_url('admin/index.php')) ?>">Dashboard</a>
          <a href="<?= e(site_url('admin/posts/index.php')) ?>">Posts</a>
          <a href="<?= e(site_url('admin/categories/index.php')) ?>">Categories</a>
          <a href="<?= e(site_url('index.php')) ?>">View site</a>
          <form method="post" action="<?= e(site_url('admin/logout.php')) ?>">
            <?= csrf_field() ?>
            <button class="nav-button" type="submit">Logout</button>
          </form>
        </nav>
        <?php if ($admin): ?><p class="sidebar-user"><?= e($admin['name']) ?></p><?php endif; ?>
      </aside>
      <main class="admin-main">
        <div class="admin-topbar"><h1><?= e($title) ?></h1></div>
    <?php
}

function admin_footer(): void
{
    ?></main></body></html><?php
}
