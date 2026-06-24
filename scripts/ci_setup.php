<?php

/**
 * CI setup script for integration testing
 * Creates SQLite database, schema, and seeds test data
 */

// Step 1: Check Kohana environment requirements
function check_environment(): void
{
    defined('SYSPATH') or define('SYSPATH', realpath(__DIR__ . '/../system') . DIRECTORY_SEPARATOR);
    defined('APPPATH') or define('APPPATH', realpath(__DIR__ . '/../application') . DIRECTORY_SEPARATOR);
    defined('EXT') or define('EXT', '.php');

    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['PHP_SELF'] = '/index.php';

    ob_start();
    $result = include __DIR__ . '/../install.php';
    $output = ob_get_clean();

    if (strpos($output, 'Your environment passed all requirements') !== false) {
        echo "✓ Environment check passed\n";
    } elseif (strpos($output, 'Kohana may not work correctly') !== false) {
        echo "✗ Environment check failed:\n$output\n";
        exit(1);
    } else {
        echo "Unexpected output from install.php:\n$output\n";
        exit(1);
    }
}

// Step 2: Create SQLite database and schema
function create_database(): void
{
    $db_path = '/tmp/kohana_test.db';

    // Remove existing database if any
    if (file_exists($db_path)) {
        unlink($db_path);
    }

    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec('CREATE TABLE IF NOT EXISTS roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(32) NOT NULL,
        description VARCHAR(255) NOT NULL
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email VARCHAR(254) NOT NULL,
        username VARCHAR(32) NOT NULL,
        password VARCHAR(64) NOT NULL,
        logins INTEGER DEFAULT 0,
        last_login INTEGER
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS user_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        user_agent VARCHAR(40) NOT NULL,
        token VARCHAR(40) NOT NULL,
        created INTEGER NOT NULL,
        expires INTEGER NOT NULL
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS roles_users (
        user_id INTEGER NOT NULL,
        role_id INTEGER NOT NULL,
        PRIMARY KEY (user_id, role_id)
    )');

    echo "✓ Database schema created\n";
}

// Step 3: Seed test data
function seed_database(): void
{
    $db = new PDO('sqlite:/tmp/kohana_test.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("INSERT OR IGNORE INTO roles (name, description) VALUES ('login', 'Login privileges, granted after account confirmation')");
    $db->exec("INSERT OR IGNORE INTO roles (name, description) VALUES ('admin', 'Administrative user, has access to everything.')");

    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $user_password = password_hash('user123', PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT OR IGNORE INTO users (email, username, password) VALUES (?, ?, ?)");
    $stmt->execute(['admin@example.com', 'admin', $admin_password]);
    $stmt->execute(['user@example.com', 'user', $user_password]);

    $db->exec('INSERT OR IGNORE INTO roles_users (user_id, role_id) VALUES (1, 2)');
    $db->exec('INSERT OR IGNORE INTO roles_users (user_id, role_id) VALUES (2, 1)');

    echo "✓ Test data seeded\n";
}

// Step 4: Write database config for SQLite
function write_database_config(): void
{
    $config = <<<'PHP'
<?php
return array(
    'default' => array(
        'type'       => 'PDO',
        'connection' => array(
            'dsn'        => 'sqlite:/tmp/kohana_test.db',
            'username'   => null,
            'password'   => null,
            'persistent' => false,
        ),
        'table_prefix' => '',
        'charset'      => 'utf8',
        'caching'      => false,
    ),
);
PHP;

    file_put_contents(__DIR__ . '/../application/config/database.php', $config);
    echo "✓ Database configured for SQLite\n";
}

// Step 5: Verify database connection from Kohana
function verify_kohana_db(): void
{
    require __DIR__ . '/../modules/unittest/bootstrap.php';

    try {
        $db = Database::instance();
        $result = $db->query(Database::SELECT, 'SELECT name FROM roles ORDER BY id');
        echo '✓ Kohana database connection works, found ' . count($result) . ' roles' . PHP_EOL;
    } catch (Exception $e) {
        echo '✗ Kohana database connection failed: ' . $e->getMessage() . PHP_EOL;
        exit(1);
    }
}

// Main execution
$steps = [
    'check_environment',
    'create_database',
    'seed_database',
    'write_database_config',
    'verify_kohana_db',
];

$exit_code = 0;
foreach ($steps as $step) {
    try {
        $step();
    } catch (Throwable $e) {
        echo "✗ Step '$step' failed: " . $e->getMessage() . PHP_EOL;
        $exit_code = 1;
        break;
    }
}

exit($exit_code);
