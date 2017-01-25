<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
  $url  = parse_url($_SERVER['REQUEST_URI']);
  $file = __DIR__ . $url['path'];
  if (is_file($file)) {
    return false;
}
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';


$app = new \Slim\App;
include_once dirname(__FILE__) . '/Constants.php';
$app->get('/profile/facebook/{id}', function (Request $request, Response $response) {
// llamada a Facebook Graph API
  $fb = new Facebook\Facebook([
    'app_id' => APP_ID,
    'app_secret' => APP_SECRET,
    'default_graph_version' => 'v2.8',
    ]);

  try {

     $url= '/'.$request->getAttribute('id').'?fields=id,first_name, last_name ';
     $facebookResponse = $fb->get($url, API_TOKEN)->getGraphUser();
     $code = 200;
 } catch(Facebook\Exceptions\FacebookResponseException $e) {

    $facebookResponse = json_encode(array('id' => $request->getAttribute('id'),
        'error' => $e->getMessage(),
        'type' => $e->getErrorType()
        ));

    $code = 404;

} catch(Facebook\Exceptions\FacebookSDKException $e) {
    $facebookResponse = json_encode(array('error' => $e->getMessage()));
    $code = 500;
}
$response->getBody()->write($facebookResponse);
$newResponse = $response->withStatus($code)->withHeader(
  'Content-type',
  'application/json; charset=utf-8'
  );
return $newResponse;
});

$app->get('/profile/facebook/', function (Request $request, Response $response) {
    $facebookResponse = json_encode(array('error' => 'Le falta el id de usuario de Facebook a la URL'));
    $response->getBody()->write($facebookResponse);

    $newResponse = $response->withStatus(400)->withHeader(
      'Content-type',
      'application/json; charset=utf-8'
      );
    return $newResponse;
});

// Run app
$app->run();
