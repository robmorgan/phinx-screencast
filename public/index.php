<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

// Add container definition for the flash component
$containerBuilder->addDefinitions(
    [
        'flash' => function () {
            $storage = [];
            return new Messages($storage);
        }
    ]
);

AppFactory::setContainer($containerBuilder->build());

$app = AppFactory::create();

// Add middleware
$app->add(
    function ($request, $next) {
        // Start PHP session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Change flash message storage
        $this->get('flash')->__construct($_SESSION);

        return $next->handle($request);
    }
);

$app->addErrorMiddleware(true, true, true);
$db = new SQLite3(__DIR__ . '/../db/guestbook.db');

// Check if the messages table exists
$tableExists = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='messages'");

if (!$tableExists) {
    // If the table doesn't exist, set up a route to return an error message
    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write("The messages table does not exist. Please check you've followed the screencast steps correctly.");
        return $response->withStatus(500);
    });

    $app->run();
    exit;
}

$app->get('/', function (Request $request, Response $response) use ($db) {
    $flash = $this->get('flash');
    $flashMessage = $flash->getFirstMessage('error');

    $messages = $db->query('SELECT * FROM messages ORDER BY created_at DESC');
    $messageList = [];
    while ($row = $messages->fetchArray(SQLITE3_ASSOC)) {
        $messageList[] = $row;
    }
    
    $renderer = new PhpRenderer(__DIR__ . '/../templates');
    return $renderer->render($response, "home.php", ['messages' => $messageList, 'flashMessage' => $flashMessage]);
});

$app->post('/sign', function (Request $request, Response $response) use ($db) {
    $data = $request->getParsedBody();
    $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
    $message = filter_var($data['message'], FILTER_SANITIZE_STRING);
    
    if ($name && $message) {
        try {
            $stmt = $db->prepare('INSERT INTO messages (name, message) VALUES (:name, :message)');
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->bindValue(':message', $message, SQLITE3_TEXT);
            $result = $stmt->execute();

            if (!$result) {
                throw new Exception($db->lastErrorMsg());
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                $this->get('flash')->addMessage('error', 'A person with this name already exists. Please use a unique name.');
            } else {
                $this->get('flash')->addMessage('error', 'An error occurred while saving your message.');
            }
        }
    }
    
    return $response->withHeader('Location', '/')->withStatus(302);
});

$app->run();
