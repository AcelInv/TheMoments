<?php
require_once __DIR__ . '/../api/auth_helper.php';
require_once __DIR__ . '/env.php';

class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            loadFloraticaEnvironment();
            if (!in_array('pgsql', PDO::getAvailableDrivers(), true)) {
                throw new RuntimeException('Driver PDO PostgreSQL (pdo_pgsql) belum aktif.');
            }
            $host = getenv('FLORATICA_DB_HOST') ?: '127.0.0.1';
            $port = getenv('FLORATICA_DB_PORT') ?: '5432';
            $database = getenv('FLORATICA_DB_NAME') ?: 'floratica';
            $username = getenv('FLORATICA_DB_USER') ?: 'postgres';
            $password = getenv('FLORATICA_DB_PASSWORD') ?: '';
            $sslMode = getenv('FLORATICA_DB_SSLMODE') ?: 'require';
            $this->conn = new PDO(
                "pgsql:host={$host};port={$port};dbname={$database};sslmode={$sslMode};options='--client_encoding=UTF8'",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
        } catch(Throwable $exception) {
            error_log("Floratica PostgreSQL connection error: " . $exception->getMessage());
            if (PHP_SAPI !== 'cli') {
                http_response_code(503);
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=UTF-8');
                }
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Layanan database sedang tidak tersedia. Periksa konfigurasi PostgreSQL.'
                ]);
                exit;
            }
        }

        return $this->conn;
    }
}
?>
