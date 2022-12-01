<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \Silez\Application;

final class TokenTest extends TestCase
{
    public function testTokenizeHandlesUrl(): void
    {
        $app = new Application();
        $tokens = $app->tokenize('/foo/bar');
        $this->assertEquals([
            '/',
            'foo',
            '/',
            'bar'
        ], $tokens);
    }
    
    public function testTokenizeHandlesUrlWithQueryString(): void
    {
        $app = new Application();
        $tokens = $app->tokenize('/foo/?bar=1');
        $this->assertEquals([
            '/',
            'foo',
            '/'
        ], $tokens);
    }
    
    public function testTokenizeHandlesUrlWithVariable(): void
    {
        $app = new Application();
        $tokens = $app->tokenize('/{bar}');
        $this->assertEquals([
            '/',
            '{bar}',
        ], $tokens);
    }
    
    public function testTokenizeHandlesUrlWithMultipleConsecutiveSlashes(): void
    {
        $app = new Application();
        $tokens = $app->tokenize('///');
        $this->assertEquals([
            '/',
            '/',
            '/'
        ], $tokens);
    }
}
