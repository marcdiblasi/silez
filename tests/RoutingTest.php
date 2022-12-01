<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \Silez\Application;
use Symfony\Component\HttpFoundation\Request;

final class RoutingTest extends TestCase
{
    public function testGetRequest(): void
    {
        $app = new Application();
        $app->get('/foo', function() {
            return 'bar';
        });

        $response = $app->handle(new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI'    => '/foo',
                'REQUEST_METHOD' => 'GET',
            ]
        ));

        $this->assertEquals('bar', $response->data);
        $this->assertEquals(200, $response->status);
        $this->assertEquals(200, $response->getCode());
    }

    public function test404(): void
    {
        $app = new Application();

        $response = $app->handle(new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI'    => '/foo',
                'REQUEST_METHOD' => 'GET',
            ]
        ));

        $this->assertEquals('404 Page not found.', $response->data);
        $this->assertEquals(404, $response->status);        
    }

    public function testError(): void
    {
        $app = new Application();
        $app->get('/foo', function(){
            throw new \Exception('error');
        });

        $response = $app->handle(new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI'    => '/foo',
                'REQUEST_METHOD' => 'GET',
            ]
        ));

        $this->assertStringStartsWith('Error: error', $response->data);
        $this->assertEquals(500, $response->status);        
    }

    public function testPostRequest(): void
    {
        $app = new Application();
        $app->post('/foo', function(Request $request) {
            return $request->get('foo');
        });

        $response = $app->handle(new Request(
            [],
            ['foo' => 'bar'],
            [],
            [],
            [],
            [
                'REQUEST_URI'    => '/foo',
                'REQUEST_METHOD' => 'POST',
            ]
        ));

        $this->assertEquals('bar', $response->data);
        $this->assertEquals(200, $response->status);
    }
}
