<?php

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response; 
use Psr\Http\Server\RequestHandlerInterface as Handler;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

use App\Session;
use App\Database;
use App\Authorization;
use App\AuthorizationException;

require __DIR__ . '/vendor/autoload.php';

[
    'dsn' => $dsn,
    'username' => $username,
    'password' => $password,
    'options' => $options
] = include_once 'config/database.php';

$loader = new FilesystemLoader('templates');
$view = new Environment($loader);

$connection = Database::getConnection($dsn, $username, $password, $options);
$authorization = new Authorization($connection);

$session = new Session();

$sessionMiddleware = function (Request $request, Handler $handler) use ($session): Response 
{
    $session->start();
    $response = $handler->handle($request);
    $session->save();

    return $response;
};

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->add($sessionMiddleware);

$app->get('/mysite/', function (Request $request, Response $response) use ($view, $session) 
{
    $body = $view->render('index.twig', [
        'success' => $session->flash('success'),
        'is_auth' => $session->getData('is_auth'),
        'currentPage' => 'index'
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/mysite/login', function (Request $request, Response $response) use ($view, $session)
{
    $body = $view->render('login.twig', [
        'is_auth' => $session->getData('is_auth'),
        'auth_message' => $session->flash('auth_message'),
        'formData' => $session->flash('formData'),
        'currentPage' => 'login'
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->post('/mysite/login-post', function (Request $request, Response $response) use ($authorization, $session)
{
    $params = (array) $request->getParsedBody();
    try {
        $authorization->login($params['username'], $params['password']);
    } catch (AuthorizationException $exception) {
        $session->setData('auth_message', $exception->getMessage());
        $session->setData('formData', $params);
        $session->setData('is_auth', false);
        return $response->withHeader('Location', '/mysite/login')
            ->withStatus(302);
    }
    $session->setData('is_auth', true);
    $session->setData('auth_message', 'Authorization is success');
    return $response->withHeader('Location', '/mysite/')
            ->withStatus(302);
});

$app->get('/mysite/registration', function (Request $request, Response $response) use ($view, $session) 
{
    $body = $view->render('registration.twig', [
        'message' => $session->flash('message'),
        'formData' => $session->flash('formData'),
        'currentPage' => 'registration'
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->post('/mysite/registration-post', function (Request $request, Response $response) use ($authorization, $session) 
{
    $params = (array) $request->getParsedBody();
    try {
        $authorization->registration($params);
    } catch (AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('formData', $params);
        $session->setData('success', false);
        return $response->withHeader('Location', '/mysite/registration')
            ->withStatus(302);
    }
    $session->setData('success', true);
    return $response->withHeader('Location', '/mysite/')
            ->withStatus(302);
});

$app->get('/mysite/logout', function (Request $request, Response $response) use ($session) {
    $session->clearAll();
    return $response->withHeader('Location', '/mysite/')
        ->withStatus(302);
});

$app->run();