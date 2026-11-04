<?php
declare(strict_types=1);

function current_admin(): ?array
{
    $admin = $_SESSION['admin'] ?? null;
    if (!is_array($admin) || !isset($admin['id'], $admin['name'], $admin['email'])) {
        return null;
    }

    $now = time();
    if (($now - (int)($_SESSION['last_activity'] ?? 0)) > 1800) {
        logout_admin();
        return null;
    }
    $_SESSION['last_activity'] = $now;

    if (($now - (int)($_SESSION['last_regeneration'] ?? 0)) > 900) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = $now;
    }

    return $admin;
}

function require_admin(): array
{
    $admin = current_admin();
    if ($admin === null) {
        redirect(site_url('admin/login.php'));
    }
    return $admin;
}

function login_admin(array $admin): void
{
    session_regenerate_id(true);
    $_SESSION = [
        'admin' => [
            'id' => (int)$admin['id'],
            'name' => (string)$admin['name'],
            'email' => (string)$admin['email'],
        ],
        'last_activity' => time(),
        'last_regeneration' => time(),
    ];
}

function logout_admin(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Lax',
        ]);
    }
    session_destroy();
}
