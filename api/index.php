<?php
/**
 * BACKEND LOGIC - Architettura LAMP (PHP 8.3 + MariaDB)
 * Versione 2.3.1 Stabile - Aggiunta Configurazione Cultura
 */

// Inclusione della configurazione esterna
if (file_exists('config.php')) {
    require_once 'config.php';
} else {
    // Fallback di emergenza se il file non esiste
    $db_config = [
        'host' => 'localhost',
        'name' => 'slide_db',
        'user' => 'root',
        'pass' => '',
    ];
}

$slug = $_GET['slug'] ?? 'spagna';

// Endpoint API integrato per comunicare con MariaDB
header('Content-Type: application/json');
try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare("SELECT content FROM presentations WHERE slug = ?");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        echo $row ? $row['content'] : json_encode(['status' => 'new']);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        $stmt = $pdo->prepare("INSERT INTO presentations (slug, content) VALUES (?, ?)
                                ON DUPLICATE KEY UPDATE content = VALUES(content)");
        $stmt->execute([$slug, $input]);
        echo json_encode(['status' => 'success']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Errore database: ' . $e->getMessage()]);
}
exit;
?>
