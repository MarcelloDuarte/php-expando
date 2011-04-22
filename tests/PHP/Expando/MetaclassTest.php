<?php

namespace Test\PHP\Expando;

require_once 'PHP/Expando/Loader.php';

class MetaclassTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    function everyClassHasAMetaclassProperty()
    {
        $class = new \MyClass;
        $this->assertInstanceOf('PHP\Expando\Metaclass', $class->metaclass);
    }

    /** @test */
    function itIsPossibleToAddMethodsToObjectsAtRuntime()
    {
        $class = new \MyClass;
        $class->metaclass->hello = function() {
            return 'Hello, World!';
        };
        $this->assertSame('Hello, World!', $class->hello());
    }

    /** @test */
    function itIsPossibleToPassParametersToMethod()
    {
        $class = new \MyClass;
        $class->metaclass->hello = function($name) {
            return "Hello, $name!";
        };
        $this->assertSame('Hello, John!', $class->hello('John'));
    }

    /** @test */
    function itIsPossibleToPrependCodeToAMethod()
    {
        $class = new \MyClass;
        $class->metaclass->hello = function($greeting) {
            return $greeting;
        };
        $class->metaclass->prependToMethod('hello', '$greeting = "Hello, $greeting!";');
        $this->assertSame('Hello, John!', $class->hello('John'));
    }

    /** @test */
    function itIsPossibleToAppendCodeToAMethod()
    {
        $class = new \MyClass;
        $class->metaclass->hello = function($name) {
            $greeting = "Hello, $name!";
        };
        $class->metaclass->appendToMethod('hello', 'return $greeting;');
        $this->assertSame('Hello, John!', $class->hello('John'));
    }
}
