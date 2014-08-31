<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;

$app->register(new SessionServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider());

// Show errors
$app['debug'] = true;

// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_pgsql',
    'host'     => 'localhost',
    'dbname'   => 'phinx_screencast',
    'user'     => 'postgres',
    'password' => '',
);

// Templates
$app->register(new TwigServiceProvider(), array(
    'twig.options'         => array(
        'cache'            => false,
        'strict_variables' => true
    ),
    'twig.path'            => array(__DIR__ . '/../resources/views')
));

$app->match('/', function () use ($app) {
    return $app['twig']->render(
        'index.html.twig',
        array(
            'posts' => $app['db']->fetchAll('SELECT * FROM posts')
        )
    );
});

$app->match('/sign', function () use ($app) {
    return $app['twig']->render(
        'sign.html.twig'
    );
});

$app->post('/submit', function (Request $request) use ($app) {
    $app['db']->insert('posts', array(
        'author'  => $request->get('author'),
        'message' => $request->get('message'),
        'created_at' => date('Y-m-d H:i:s')
    ));
    $app['session']->getFlashBag()->add('notice', 'Profile updated');
    return $app->redirect('/');
});

return $app;
