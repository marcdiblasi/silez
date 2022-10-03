<?php

/*
 * This file is part of the Silez framework.
 *
 * Author: Marc DiBlasi <marc.diblasi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silez;

use \Pimple\ServiceProviderInterface;

use Silez\Response\RedirectResponse;
use Silez\Response\JsonResponse;

use Symfony\Component\HttpFoundation\Request;

class Application extends \Pimple\Container
{
    public array $routes = [];
    public array $providers = [];
    public array $before = [];
    public array $after = [];
    public array $error = [];

    const SEPARATORS = ['/'];

    public function __construct(array $values = [])
    {
        parent::__construct();

        $defaults = [
            'debug'   => false,
            'charset' => 'UTF-8',
        ];

        foreach ($defaults as $key => $value) {
            $this[$key] = $value;
        }

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }
    }

    public function register(ServiceProviderInterface $provider, array $values = []) : Application
    {
        $this->providers[] = $provider;

        $provider->register($this);

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }

        return $this;
    }

    public function before(callable $callback) : void
    {
        $this->before[] = $callback;
    }

    public function after(callable $callback) : void
    {
        $this->after[] = $callback;
    }

    public function error(callable $callback) : void
    {
        $this->error[] = $callback;
    }

    protected function addRoute(string $method, string $pattern, callable $to)
    {
        $this->routes[] = [
            'method'  => strtolower($method),
            'pattern' => $pattern,
            'to'      => $to,
            'regexp'  => preg_replace(
                '#\\\{\w\\\}#',
                '(.*)',
                preg_quote($pattern)
            ),
        ];
    }

    public function match(string $pattern, callable $to) : void
    {
        $this->addRoute('match', $pattern, $to);
    }

    public function get(string $pattern, callable $to) : void
    {
        $this->addRoute('get', $pattern, $to);
        $this->addRoute('head', $pattern, $to);
    }

    public function post(string $pattern, callable $to) : void
    {
        $this->addRoute('post', $pattern, $to);
    }

    public function put(string $pattern, callable $to) : void
    {
        $this->addRoute('put', $pattern, $to);
    }

    public function delete(string $pattern, callable $to) : void
    {
        $this->addRoute('delete', $pattern, $to);
    }

    public function options(string $pattern, callable $to) : void
    {
        $this->addRoute('options', $pattern, $to);
    }

    public function patch(string $pattern, callable $to) : void
    {
        $this->addRoute('patch', $pattern, $to);
    }

    public function abort(int $statusCode, string $message, array $headers = [])
    {
        throw new Response($message, $statusCode, $headers);
    }

    public function redirect(string $url, int $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    public function json(array $data = [], int $status = 200, array $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    public function run()
    {
        $matches = [];
        try {
            $urlTokens = $this->tokenize($_SERVER['REQUEST_URI']);
            $method    = strtolower($_SERVER['REQUEST_METHOD']);
            $request   = Request::createFromGlobals();
            $response  = null;

            foreach ($this->routes as $route) {
                $vars = [];
                if ($method === $route['method'] || 'match' === $route['method']) {
                    $routeTokens = $this->tokenize($route['pattern']);

                    if (count($routeTokens) !== count($urlTokens)) {
                        continue;
                    }

                    for ($i = 0; $i < count($routeTokens); $i++) {
                        if ('{' === $routeTokens[$i][0]) {
                            $vars[trim($routeTokens[$i], '{}')] = $urlTokens[$i];
                        } elseif ($routeTokens[$i] !== $urlTokens[$i]) {
                            break;
                        }
                    }

                    // Match!
                    if (count($routeTokens) === $i) {
                        foreach ($this->before as $before) {
                            $request = call_user_func($before, $request);
                        }

                        $response = call_user_func_array($route['to'], $vars);

                        if (is_string($response)) {
                            $newResponse = new Response($response);
                            $response    = $newResponse;
                        }

                        foreach ($this->after as $after) {
                            $response = call_user_func($after, $request, $response);
                        }
                    }
                }
            }

            if (is_null($response)) {
                throw new Response('404 Page not found.', 404);
            }
        } catch (\Exception $e) {
            if ($e instanceof Response) {
                $response = $e;
            }

            foreach ($this->error as $error) {
                $returnValue = call_user_func($error, $e, $e->getCode());

                if ($returnValue instanceof Response) {
                    $response = $returnValue;
                }
            }
        }

        http_response_code($response->status);

        foreach ($response->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        if ('head' !== strtolower($_SERVER['REQUEST_METHOD'])) {
            echo $response->data;
        }

        exit;
    }

    protected function tokenize(string $url) : array
    {
        $tokens = [];
        $originalUrl = $url;

        // Get rid of # and everything after
        if (false !== ($pos = strpos($url, '#'))) {
            $url = substr($url, 0, $pos);
        }

        while (strlen($url)) {
            if (in_array($url[0], $this::SEPARATORS)) {
                $tokens[] = $url[0];

                $url = substr($url, 1);
            } elseif (preg_match('#^[a-zA-Z0-9\._-]+#', $url, $matches)) {
                $tokens[] = $matches[0];

                $url = substr($url, strlen($matches[0]));
            } elseif ('{' === $url[0]) {
                $depth = 1;
                $pointer = 1;

                while ($pointer < strlen($url)) {
                    if ('{' === $url[$pointer]) {
                        $depth++;
                    } elseif ('}' === $url[$pointer]) {
                        $depth--;
                    }

                    $pointer++;

                    if (!$depth) {
                        $tokens[] = substr($url, 0, $pointer);

                        $url = substr($url, $pointer);
                        break;
                    }
                }

                if ($pointer === strlen($url)) {
                    throw new \Exception('Tokenizer: couldn\'t find the end of '
                        . 'variable. Are you missing a "}"?');
                }
            } else {
                throw new \Exception('Tokenizer: no idea what this is "'
                    . $url[0] . '"');
            }
        }

        return $tokens;
    }
}
