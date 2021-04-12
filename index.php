<?php

use App\Auth;
use App\AuthException;
use App\Helper;
use App\DataBase;
use App\Session;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

define('CONFIG_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'config');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

Helper::displayErrors(1);

$app = AppFactory::create();
$app->addBodyParsingMiddleware(); // parsed super globals variables
$template_loader = new FilesystemLoader('templates');
$twig = new Environment($template_loader);

$session = new Session();
$session_middleware = function (Request $request, RequestHandler $handler) use ($session) {
    $session->start();
    $response = $handler->handle($request);
    $session->save();
    return $response;
};

$app->add($session_middleware);

$app->get('/', function (Request $request, Response $response) use ($twig, $session) {
    $body = $twig->render('index.twig', [
        'user' => $session->getData('user')
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/login', function (Request $request, Response $response) use ($twig, $session) {
    $body = $twig->render('login.twig', [
        'message' => $session->flush('message'),
        'input_fill' => $session->flush('inputs_fill'),
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/register', function (Request $request, Response $response) use ($twig, $session) {
    $body = $twig->render('register.twig', [
        'message' => $session->flush('message'),
        'input_fill' => $session->flush('inputs_fill'),
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/logout', function (Request $request, Response $response) use ($session) {
    $session->setData('user', null);
    return $response->withHeader('Location', '/')->withStatus(302);
});


// Post
$app->post('/register-post', function (Request $request, Response $response) use ($session) {
    $params = (array) $request->getParsedBody();
    $db = new DataBase();
    $auth = new Auth($db, $session);

    try {
        if ($auth->register($params)) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        return $response->withHeader('Location', '/')->withStatus(302);

    } catch (AuthException $e) {
        $session->setData('message', $e->getMessage());
        $session->setData('inputs_fill', $params);
        return  $response->withHeader('Location', '/register')->withStatus(302);
    }
});

$app->post('/login-post', function (Request $request, Response $response) use ($session) {
    $params = (array) $request->getParsedBody();
    $db = new DataBase();
    $auth = new Auth($db, $session);

    try {
        if ($auth->login($params)) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        return $response->withHeader('Location', '/')->withStatus(302);

    } catch (AuthException $e) {
        $session->setData('message', $e->getMessage());
        $session->setData('inputs_fill', $params);
        return  $response->withHeader('Location', '/login')->withStatus(302);
    }
});


$app->run();