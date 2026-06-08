<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
fleetAuthRequireAdmin();

const ADMIN_USERNAME_PREFIX = 'BUESMISadmin';

function adminStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function adminPageUrl(): string
{
    return '/fleet-system/modules/admins/index.php';
}

function adminHandlerUrl(): string
{
    return '/fleet-system/handlers/admin.php';
}

function adminSetFlash(array $payload): void
{
    adminStartSession();
    $_SESSION['admin_flash'] = $payload;
}

function adminPullFlash(): ?array
{
    adminStartSession();

    if (!isset($_SESSION['admin_flash']) || !is_array($_SESSION['admin_flash'])) {
        return null;
    }

    $flash = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);

    return $flash;
}

function adminGenerateUsername(PDO $pdo): string
{
    $statement = $pdo->prepare(
        'SELECT username
        FROM users
        WHERE username LIKE :prefix
            AND role = \'admin\'
        FOR UPDATE'
    );
    $statement->execute(['prefix' => ADMIN_USERNAME_PREFIX . '%']);

    $nextNumber = 1;
    foreach ($statement->fetchAll() as $row) {
        $username = (string) ($row['username'] ?? '');
        if (preg_match('/(\d{3})$/', $username, $matches)) {
            $nextNumber = max($nextNumber, ((int) $matches[1]) + 1);
        }
    }

    if ($nextNumber > 999) {
        throw new RuntimeException('Admin username numbers have been exhausted.');
    }

    return ADMIN_USERNAME_PREFIX . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
}

function adminGenerateOneTimePassword(): string
{
    return 'Admin-' . substr(bin2hex(random_bytes(8)), 0, 12);
}

function adminFetchPageData(): array
{
    $flash = adminPullFlash();
    $notification = $flash['notification'] ?? null;
    $credentials = $flash['credentials'] ?? null;
    $formData = $flash['form_data'] ?? [];
    $admins = [];

    try {
        $statement = fleetDb()->query(
            'SELECT id, username, name, email, status, must_change_password, last_login_at, created_at
            FROM users
            WHERE role = \'admin\'
            ORDER BY created_at DESC, id DESC'
        );

        foreach ($statement->fetchAll() as $row) {
            $admins[] = [
                'id' => (int) $row['id'],
                'username' => (string) $row['username'],
                'name' => (string) $row['name'],
                'email' => (string) $row['email'],
                'status' => ucfirst((string) $row['status']),
                'status_value' => (string) $row['status'],
                'must_change_password' => (int) $row['must_change_password'] === 1 ? 'Yes' : 'No',
                'last_login_at' => $row['last_login_at'] ? date('d M Y H:i', strtotime((string) $row['last_login_at'])) : 'No login recorded',
                'created_at' => date('d M Y', strtotime((string) $row['created_at'])),
            ];
        }
    } catch (Throwable $exception) {
        $notification = [
            'type' => 'error',
            'title' => 'Unable to load admins',
            'message' => 'Admin accounts could not be loaded right now.',
        ];
    }

    return [
        'admins' => $admins,
        'hasAdmins' => $admins !== [],
        'adminNotification' => $notification,
        'adminCredentials' => is_array($credentials) ? $credentials : null,
        'adminFormData' => $formData,
        'adminFormAction' => adminHandlerUrl(),
    ];
}

function adminHandleCreate(): void
{
    $formData = [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'status' => strtolower(trim((string) ($_POST['status'] ?? 'active'))),
    ];

    try {
        if ($formData['name'] === '') {
            throw new RuntimeException('Admin full name is required.');
        }

        if ($formData['email'] === '' || filter_var($formData['email'], FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('A valid admin email is required.');
        }

        if (!in_array($formData['status'], ['active', 'inactive', 'suspended'], true)) {
            throw new RuntimeException('Please select a valid admin status.');
        }

        $pdo = fleetDb();
        $pdo->beginTransaction();

        $username = adminGenerateUsername($pdo);
        $oneTimePassword = adminGenerateOneTimePassword();
        $statement = $pdo->prepare(
            'INSERT INTO users (
                username,
                name,
                email,
                password_hash,
                role,
                status,
                must_change_password
            ) VALUES (
                :username,
                :name,
                :email,
                :password_hash,
                \'admin\',
                :status,
                1
            )'
        );
        $statement->execute([
            'username' => $username,
            'name' => $formData['name'],
            'email' => $formData['email'],
            'password_hash' => password_hash($oneTimePassword, PASSWORD_DEFAULT),
            'status' => $formData['status'],
        ]);

        $pdo->commit();

        adminSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Admin login created',
                'message' => 'Username: ' . $username . ' | One-time password: ' . $oneTimePassword,
            ],
            'credentials' => [
                'username' => $username,
                'one_time_password' => $oneTimePassword,
            ],
        ]);
    } catch (RuntimeException $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        adminSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Admin was not added',
                'message' => $exception->getMessage(),
            ],
            'form_data' => $formData,
        ]);
    } catch (PDOException $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        adminSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Admin was not added',
                'message' => (int) $exception->getCode() === 23000
                    ? 'That admin email or username already exists.'
                    : 'The admin account could not be created.',
            ],
            'form_data' => $formData,
        ]);
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        adminSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Admin was not added',
                'message' => 'A system error occurred while creating the admin account.',
            ],
            'form_data' => $formData,
        ]);
    }

    header('Location: ' . adminPageUrl());
    exit;
}

function adminHandleRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . adminPageUrl());
        exit;
    }

    $action = trim((string) ($_POST['admin_action'] ?? 'create'));

    if ($action === 'create') {
        adminHandleCreate();
    }

    header('Location: ' . adminPageUrl());
    exit;
}

if (basename(__FILE__) === basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    adminHandleRequest();
}
