<?php

/*
 * This file is part of the Silez framework.
 *
 * Author: Marc DiBlasi <marc.diblasi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silez\Provider;

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

use \Twig\Environment;
use \Twig\Loader\ChainLoader;
use \Twig\Loader\ArrayLoader;
use \Twig\Loader\FilesystemLoader;
use \Twig\Extension\DebugExtension;

class TwigServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['twig.options'] = [];
        $app['twig.form.templates'] = ['form_div_layout.html.twig'];
        $app['twig.path'] = [];
        $app['twig.templates'] = [];

        $app['twig'] = function ($app) {
            $app['twig.options'] = array_replace(
                [
                    'charset'          => $app['charset'],
                    'debug'            => $app['debug'],
                    'strict_variables' => $app['debug'],
                ], $app['twig.options']
            );

            $twig = new Environment($app['twig.loader'], $app['twig.options']);

            $twig->addGlobal('app', $app);

            if ($app['debug']) {
                $twig->addExtension(new DebugExtension());
            }

            return $twig;
        };

        $app['twig.loader.filesystem'] = function ($app) {
            return new FilesystemLoader($app['twig.path']);
        };

        $app['twig.loader.array'] = function ($app) {
            return new ArrayLoader($app['twig.templates']);
        };

        $app['twig.loader'] = function ($app) {
            return new ChainLoader([
                $app['twig.loader.array'],
                $app['twig.loader.filesystem'],
            ]);
        };
    }

    public function boot(Application $app)
    {
    }
}
