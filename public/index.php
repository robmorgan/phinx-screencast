<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require_once __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$db = new SQLite3(__DIR__ . '/../guestbook.db');
$db->exec('CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

$app->get('/', function (Request $request, Response $response) use ($db) {
    $messages = $db->query('SELECT * FROM messages ORDER BY created_at DESC');
    $messageList = [];
    while ($row = $messages->fetchArray(SQLITE3_ASSOC)) {
        $messageList[] = $row;
    }
    
    $renderer = new PhpRenderer(__DIR__ . '/../templates');
    return $renderer->render($response, "home.php", ['messages' => $messageList]);
});

$app->post('/sign', function (Request $request, Response $response) use ($db) {
    $data = $request->getParsedBody();
    $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
    $message = filter_var($data['message'], FILTER_SANITIZE_STRING);
    
    if ($name && $message) {
        $stmt = $db->prepare('INSERT INTO messages (name, message) VALUES (:name, :message)');
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':message', $message, SQLITE3_TEXT);
        $stmt->execute();
    }
    
    return $response->withHeader('Location', '/')->withStatus(302);
});

$app->run();