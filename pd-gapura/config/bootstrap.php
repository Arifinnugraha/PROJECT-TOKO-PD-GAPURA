<?php
declare(strict_types=1);

// Cookie sesi aman untuk penggunaan lokal maupun hosting HTTPS.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('PDGAPURA_SESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$config = require __DIR__ . '/database.php';
$requiredConfig = ['host', 'dbname', 'username', 'password'];
foreach ($requiredConfig as $key) {
    if (!isset($config[$key]) || $config[$key] === '') {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Konfigurasi koneksi Supabase belum lengkap. Periksa environment variables SUPABASE_DB_*.']);
        exit;
    }
}

if (!extension_loaded('pdo_pgsql')) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Ekstensi PHP pdo_pgsql belum aktif pada server hosting.']);
    exit;
}

$dsn = sprintf(
    'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
    $config['host'],
    $config['port'],
    $config['dbname'],
    $config['sslmode']
);

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    // Konsistenkan timestamp tampilan dan transaksi dengan waktu Indonesia.
    $pdo->exec("SET TIME ZONE 'Asia/Jakarta'");
} catch (PDOException $e) {
    error_log('Supabase PostgreSQL connection error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Database Supabase belum terhubung. Periksa host, port, database, username, password, SSL, dan ekstensi pdo_pgsql pada hosting.']);
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function respond(int $status, array $payload): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) return [];
    $data = json_decode($raw, true);
    if (!is_array($data)) respond(400, ['success' => false, 'message' => 'Format JSON tidak valid.']);
    return $data;
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireAuth(): array
{
    $user = currentUser();
    if (!$user) respond(401, ['success' => false, 'message' => 'Sesi berakhir. Silakan login kembali.']);
    return $user;
}

function requireRole(array $roles): array
{
    $user = requireAuth();
    if (!in_array($user['role'], $roles, true)) respond(403, ['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk tindakan ini.']);
    return $user;
}

function logActivity(PDO $pdo, int $userId, string $aksi, string $deskripsi): void
{
    $stmt = $pdo->prepare('INSERT INTO aktivitas_log (id_user, aksi, deskripsi, waktu) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$userId, $aksi, $deskripsi]);
}

function hasReportAccess(PDO $pdo, array $user): bool
{
    if (($user['role'] ?? '') === 'pemilik') return true;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM permintaan_akses_laporan WHERE id_user=? AND status='Disetujui'");
    $stmt->execute([(int)$user['id_user']]);
    return (int)$stmt->fetchColumn() > 0;
}

function positiveInt(mixed $value): bool
{
    return filter_var($value, FILTER_VALIDATE_INT) !== false && (int)$value > 0;
}

function nonNegativeInt(mixed $value): bool
{
    return filter_var($value, FILTER_VALIDATE_INT) !== false && (int)$value >= 0;
}

function money(mixed $value): bool
{
    return is_numeric($value) && (float)$value >= 0;
}
