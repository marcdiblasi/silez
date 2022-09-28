<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new \Silez\Application();

# A simple route
$app->get('/foo', function() use ($app) {
    return 'foo';
});

# You can use Twig!
$app->register(new \Silez\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__,
]);

# A route with a variable that returns a Twig template
$app->get('/foo/{bar}', function($bar) use ($app) {
    return $app['twig']->render('example.html', ['content' => $bar]);
    #return $bar;
});

# A redirect
$app->get('/redir', function() use ($app) {
    return $app->redirect('/test');
});

# Some JSON
$app->get('/json', function() use ($app) {
    return $app->json(['foo' => 'bar']);
});

$app->run();
