<?php
/**
 * PHP_Expando
 * 
 * PHP_Expando is made available here with absolute NO WARRANTS and under the
 * terms of the new BSD License listed bellow.
 *         
 * Copyright (c) 2011, Marcello Duarte <marcello.duarte@gmail.com>
 * 
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * * Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * 
 * * Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * 
 * * Neither the name of Marcello Duarte nor the names of other
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * @package    PHP_Expando
 * @subpackage Metaclass
 * @author     Marcello Duarte <marcello.duarte@gmail.com>
 * @copyright  2011 Marcello Duarte <marcello.duarte@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.github.com/MarcelloDuarte/php-expando
 * @since      File available since Release 0.1.0
 */
namespace PHP\Expando\Metaclass;

/**
 * @see \PHP\Expando\Reflection\ReflectionClosure
 */
use \PHP\Expando\Reflection\ReflectionClosure as Reflected;

/**
 * @link http://php.net/manual/en/class.badmethodcallexception.php
 */
use \BadMethodCallException as BadMethodCall;

/**
 * ClassScope
 * 
 * Every class intercepted by PHP_Expando loader will have a Metaclass object
 * in its public metaclass property, available to objects and class scope
 * via object operator or scope resolution operator. ClassScope is the one
 * used by the class scope.
 * 
 * @package Metaclass
 * @author  Marcello Duarte <marcello.duarte@gmail.com>
 */
class ClassScope extends \PHP\Expando\Metaclass
{
    /**
     * The intercepted class
     * 
     * @var string
     */
    protected $_class;
    
    /**
     * Methods added to the class
     * 
     * @var array<Closure>
     */
    protected static $_classMethods = array();

    /**
     * Adds methods as closures to an class 
     *
     * Example:
     * <code>
     * SomeClass::$metaclass->addMethod('newMethod', function() {
     *    // do something
     * });
     * </code>
     *
     * @param string   $method
     * @param \Closure $body
     * 
     * @return \PHP\Expando\Metaclass
     */
    public function addMethod($method, \Closure $body)
    {
        self::$_classMethods[$this->_class][$method] = $body;
    }
    
    /**
     * Returns a method added earlier on as a closure
     * 
     * @param string $class
     * @param string $method
     * 
     * @return \Closure
     */
    public static function getMethod($class, $method)
    {
        return self::$_classMethods[$class][$method];
    }

    /**
     * Appends code, passed as string, to a method. WARNING: At the moment you
     * can only append code to methods you have dynamically added to the
     * object
     * 
     * @param string $method
     * @param string $codeToAppend
     * 
     * @return \PHP\Expando\ClassScope
     */
    public function appendToMethod($method, $codeToAppend)
    {
        self::assertClassMethodExists($this->_class, $method);

        $reflected = new Reflected(
            self::$_classMethods[$this->_class][$method]
        );

        eval (
         ' self::$_classMethods[$this->_class][$method] =  ' .
              "{$reflected->getSignature()} { " .
              $reflected->getBody() . " $codeToAppend " .
         "};"
        );
        
        return $this;
    }

    /**
     * Prepends code, passed as string, to a method. WARNING: At the moment you
     * can only prepend code to methods you have dynamically added to the
     * class
     * 
     * @param string $method
     * @param string $codeToPrepend
     * 
     * @return \PHP\Expando\ClassScope
     */
    public function prependToMethod($method, $codeToPrepend)
    {
        self::assertClassMethodExists($this->_class, $method);

        $reflected = new Reflected(
            self::$_classMethods[$this->_class][$method]
        );

        eval (
         ' self::$_classMethods[$this->_class][$method] =  ' .
             "{$reflected->getSignature()} {
             $codeToPrepend {$reflected->getBody()}
          };"
        );
        
        return $this;
    }

    /**
     * Intercepts call from within the class intercepted. Proxies method calls
     * to methods added to classes via its metaclass
     * 
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        self::assertClassMethodExists($this->_class, $method);
        return \call_user_func_array(
            self::$_classMethods[$this->_class][$method],
            $args
        );
    }

    /**
     * Checks whether the method has been added in class scope
     * 
     * @param string $class
     * @param string $method
     * @throws BadMethodCall
     */
    public static function assertClassMethodExists($class, $method)
    {
        if (!isset(self::$_classMethods[$class]) ||
            !\in_array(
                \strtolower($method),
                \array_keys(
                    \array_change_key_case(self::$_classMethods[$class])
                )
            )) {
            throw new BadMethodCall("Invalid method $method.");
        }
    }
}

