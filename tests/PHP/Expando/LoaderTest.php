<?php

namespace Test\PHP\Expando;

require_once 'PHP/Expando/Loader.php';

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    function setUp()
    {
        \PHP\Expando\Loader::register();
    }

    /** @test */
    function itIsPossibleToLoadAClassWithConstructor()
    {
        $class = new \AClassWithConstructor('John');
        $class->metaclass->hello = function() {
            return 'Hello, World!';
        };
        $this->assertSame('Hello, World!', $class->hello());
    }

    /** @test */
    function itIsPossibleToLoadAClassWithGetter()
    {
        $class = new \AClassWithMagicGet;
        $class->metaclass->hello = function() {
            return 'Hello, World!';
        };
        $this->assertSame('Hello, World!', $class->hello());
        $this->assertSame('foo', $class->foo);
    }

    /** @test */
    function itIsPossibleToLoadAClassWithCall()
    {
        $class = new \AClassWithMagicCall;
        $class->metaclass->hello = function() {
            return 'Hello, World!';
        };
        $this->assertSame('Hello, World!', $class->hello());
        $this->assertSame('foo(bar)', $class->foo('bar'));
    }
    
    function tearDown()
    {
        \PHP\Expando\Loader::unregister();
    }
}
