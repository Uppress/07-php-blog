<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/app/db.php';

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php create-admin.php \"Your Name\" you@example.com\n");
    exit(2);
}

$name = trim($argv[1]);
$email = strtolower(trim($argv[2]));
if ($name === '' || mb_strlen($name, 'UTF-8') > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Please provide a name (max 120 characters) and a valid email address.\n");
    exit(2);
}

try {
    $pdo = db();
    fwrite(STDOUT, "Password: ");
    $password = function_exists('readline')
        ? trim((string)readline())
        : trim((string)fgets(STDIN));
    if (strlen($password) < 12) {
        fwrite(STDERR, "Password must be at least 12 characters.\n");
        exit(2);
    }

    $pdo->beginTransaction();
    $existing = $pdo->query('SELECT id FROM admins LIMIT 1 FOR UPDATE')->fetchColumn();
    if ($existing !== false) {
        $pdo->rollBack();
        fwrite(STDERR, "An admin already exists. This script only creates the first admin.\n");
        exit(1);
    }
    $stmt = $pdo->prepare('INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    $pdo->commit();
    fwrite(STDOUT, "Admin created for {$email}.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, "Unable to create admin: " . $exception->getMessage() . "\n");
    exit(1);
}
