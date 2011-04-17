PHP_Expando
===========

**PHP_Expando** is a library that allows adding methods to classes or objects using a neat closure syntax.

How does it work
----------------

PHP_Expando provides an autoloader that injects a metaclass property (both static and object scope). The metaclass when used gives you a reference to the an Expando Metaclass which allows you to add methods, much in the faction of prototypes in Javasript.

Registering the loader
----------------------

    <?php
    require_once 'PHP/Expando/Loader.php';
    \PHP\Expando\Loader::register();

Usage with class scope
----------------------

    <?php
   
    \MyClass::$metaclass->hello = function() {
        return 'Hello, World!';
    };
    $class = new \MyClass;
    assertSame('Hello, World!', $class->hello());

Usage with object scope
-----------------------

	<?php
   
    $class = new \MyClass;
    $class->metaclass->hello = function() {
        return 'Hello, World!';
    };
    assertSame('Hello, World!', $class->hello());

Append to method
----------------

    <?php
    
    $class = new \MyClass;
    $class->metaclass->hello = function($name) {
        $greeting = "Hello, $name!";
    };
    $class->metaclass->appendToMethod('hello', 'return $greeting;');
    assertSame('Hello, John!', $class->hello('John'));

Prepend to method
-----------------

    $class = new \MyClass;
    $class->metaclass->hello = function($greeting) {
        return $greeting;
    };
    $class->metaclass->prependToMethod('hello', '$greeting = "Hello, $greeting!";');
    assertSame('Hello, John!', $class->hello('John'));
