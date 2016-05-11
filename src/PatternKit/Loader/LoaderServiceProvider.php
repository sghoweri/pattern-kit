<?php

namespace PatternKit\Loader;

use Silex\Application;
use Silex\ServiceProviderInterface;

class LoaderServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['loader'] = $app->share(
          function () {
              return new FileLoader();
          }
        );
    }

    public function boot(Application $app)
    {
    }

}
