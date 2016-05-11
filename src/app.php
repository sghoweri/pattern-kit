<?php

use Silex\Application;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Carbon\Carbon;
use PatternKit\Loader;
use PatternKit\Navigation\Navigation;
use PatternKit\Helpers\TwigHelper;


date_default_timezone_set('America/Los_Angeles');

define('ROOT_PATH', __DIR__.'/..');


$app = new Application();
$app->register(new YamlConfigServiceProvider('./.pk-config.yml'));
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(
  new HttpCacheServiceProvider(),
  array('http_cache.cache_dir' => ROOT_PATH.'/storage/cache',)
);

$app->register(
  new MonologServiceProvider(),
  array(
    'monolog.logfile' => ROOT_PATH.'/storage/logs/'.Carbon::now(
        'America/Los_Angeles'
      )->format('Y-m-d').'.log',
    'monolog.name' => 'application',
  )
);

//handling CORS preflight request
$app->before(
  function (Request $request) {
      if ($request->getMethod() === 'OPTIONS') {
          $response = new Response();
          $response->headers->set('Access-Control-Allow-Origin', '*');
          $response->headers->set(
            'Access-Control-Allow-Methods',
            'GET,POST,PUT,DELETE,OPTIONS'
          );
          $response->headers->set(
            'Access-Control-Allow-Headers',
            'Content-Type'
          );
          $response->setStatusCode(200);

          return $response->send();
      }
  },
  Application::EARLY_EVENT
);

//handling CORS respons with right headers
$app->after(
  function (Request $request, Response $response) {
      $response->headers->set('Access-Control-Allow-Origin', '*');
      $response->headers->set(
        'Access-Control-Allow-Methods',
        'GET,POST,PUT,DELETE,OPTIONS'
      );
  }
);

// //accepting JSON
// $app->before(function (Request $request) {
//     if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
//         $data = json_decode($request->getContent(), true);
//         $request->request->replace(is_array($data) ? $data : array());
//     }
// });

$app['debug'] = $app['config']['dev'];


//// Register Twig

$test = new TwigHelper();

$app->register(
  new Silex\Provider\TwigServiceProvider(),
  array(
    'twig.path' => TwigHelper::getPaths($app['config']['paths']['templates']),
    'twig.options' => array(
      'strict_variables' => false,
    ),
  )
);

$app->register(
  new PatternKit\Loader\LoaderServiceProvider(),
  array('type' => 'FileLoader')
);

// Mount Routes
$app->mount('/schema', new PatternKit\SchemaControllerProvider());
$app->mount('/api', new PatternKit\ApiControllerProvider());
$app->mount('/tests', new PatternKit\TestsControllerProvider());
$app->mount('/sg', new PatternKit\StyleGuideControllerProvider());

$app->get(
  '/',
  function () use ($app) {
      $data = array();
      $data['nav'] = Navigation::getNav('/');

      return $app['twig']->render('display-schema.twig', $data);
  }
);

$app->error(
  function (\Exception $e, $code) use ($app) {
      $app['monolog']->addError($e->getMessage());
      $app['monolog']->addError($e->getTraceAsString());

      return new JsonResponse(
        array(
          'statusCode' => $code,
          'message' => $e->getMessage(),
          'stacktrace' => $e->getTraceAsString(),
        )
      );
  }
);
