<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';

if (current_admin() !== null) {
    redirect(site_url('admin/index.php'));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && $password !== '') {
        $stmt = db()->prepare('SELECT id, name, email, password_hash FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
                $update = db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
                $update->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
            }
            login_admin($admin);
            redirect(site_url('admin/index.php'));
        }
    }
    $error = 'Email or password is incorrect.';
}

page_header('Admin login');
?>
<section class="auth-card">
  <p class="eyebrow">Administration</p>
  <h1>Sign in</h1>
  <?php if ($error): ?><p class="alert alert-error" role="alert"><?= e($error) ?></p><?php endif; ?>
  <form method="post" action="<?= e(site_url('admin/login.php')) ?>">
    <?= csrf_field() ?>
    <label for="email">Email</label>
    <input id="email" name="email" type="email" maxlength="190" autocomplete="username" required value="<?= e((string)($_POST['email'] ?? '')) ?>">
    <label for="password">Password</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required>
    <button class="button button-full" type="submit">Sign in</button>
  </form>
</section>
<?php page_footer(); ?>
