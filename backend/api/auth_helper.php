<?php
declare(strict_types=1);

function applyApiSecurityHeaders(): void {
    static $applied = false;
    if ($applied || headers_sent()) return;
    $applied = true;
    header_remove('Access-Control-Allow-Origin');
    header_remove('Access-Control-Allow-Credentials');
    header_remove('Access-Control-Allow-Headers');
    header_remove('Access-Control-Allow-Methods');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'; base-uri 'none'");
    header('Cache-Control: no-store, private');
}

function requestUsesHttps(): bool {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (getenv('FLORATICA_COOKIE_SECURE') === 'true')
        || (getenv('FLORATICA_TRUST_PROXY') === 'true'
            && (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'));
}

function startSecureSession(): void {
    if (session_status() !== PHP_SESSION_NONE) return;
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', requestUsesHttps() ? '1' : '0');
    session_name('floratica_session');
    session_start();
}

function csrfToken(): string {
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function requireCsrfToken(): void {
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? '', ['POST', 'PUT', 'DELETE'], true)) {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
        exit;
    }
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.']);
        exit;
    }
}

function rateLimit(string $key, int $limit, int $windowSeconds): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'floratica-rate-' . hash('sha256', $key . '|' . $ip) . '.json';
    $handle = @fopen($file, 'c+');
    if ($handle === false) return;
    flock($handle, LOCK_EX);
    $stored = stream_get_contents($handle);
    $attempts = json_decode($stored ?: '[]', true);
    $now = time();
    $attempts = is_array($attempts) ? array_values(array_filter($attempts, static fn($at) => is_int($at) && $at > $now - $windowSeconds)) : [];
    if (count($attempts) >= $limit) {
        flock($handle, LOCK_UN); fclose($handle);
        http_response_code(429);
        echo json_encode(['status' => 'error', 'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.']);
        exit;
    }
    $attempts[] = $now;
    ftruncate($handle, 0); rewind($handle); fwrite($handle, json_encode($attempts));
    flock($handle, LOCK_UN); fclose($handle);
}

applyApiSecurityHeaders();
startSecureSession();

function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.']);
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Area khusus admin.']);
        exit;
    }
}

function checkUserOrAdmin($userIdToCheck): void {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== 'admin' && (int) $_SESSION['user_id'] !== (int) $userIdToCheck) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Anda tidak berhak mengakses data ini.']);
        exit;
    }
}
?>
