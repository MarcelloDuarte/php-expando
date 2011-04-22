<?php

namespace Test\PHP\Expando\Metaclass;

class ClassScopeTest extends \PHPUnit_Framework_TestCase
{	
	/** @test */
    function itIsPossibleToAddMethodsAtRuntime()
    {
        \MyClass::$metaclass->hello = function() {
            return 'Hello, World!';
        };
        $class = new \MyClass;
        $this->assertSame('Hello, World!', $class->hello());
    }

    /** @test */
    function itIsPossibleToPrependCodeToAMethod()
    {
        \MyClass::$metaclass->hello = function($greeting) {
            return $greeting;
        };
        \MyClass::$metaclass->prependToMethod('hello', '$greeting = "Hello, $greeting!";');
        $class = new \MyClass;
        $this->assertSame('Hello, John!', $class->hello('John'));
    }
}

