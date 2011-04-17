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
 * @subpackage Parser
 * @author     Marcello Duarte <marcello.duarte@gmail.com>
 * @copyright  2011 Marcello Duarte <marcello.duarte@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.github.com/MarcelloDuarte/php-expando
 * @since      File available since Release 0.1.0
 */
namespace PHP\Expando\Modifier;

/**
 * @see \PHP\Expando\Reflection\ReflectionClass
 */
use \PHP\Expando\Reflection\ReflectionClass;

/**
 * Proxy
 * 
 * Base class that adds functionalities to modifiers
 * 
 * @package Metaclass
 * @author  Marcello Duarte <marcello.duarte@gmail.com>
 */
abstract class Proxy
{
    /**
     * The reflecion object 
     *
     * @var \PHP\Expando\Reflection\ReflectionClass
     */
    protected $_reflected;
    
    /**
     * Path of the class
     * 
     * @var string
     */
    protected $_path;
    
    /**
     * Proxy is created with the path
     * 
     * @param string $path
     */
    public function __construct($path)
    {
        $this->_path = $path;
    }
    
    /**
     * Proxies need to specify the code to modify
     */
    abstract protected function codeToModify();
    
    /**
     * Proxies need to specify the default source in case there's nothing to
     * modify
     */
    abstract protected function defaultSource();
    
    /**
     * Modifies the source code prepending the code to modify, or just add
     * the default source
     * 
     * @param string $source
     */
    public function modify(&$source)
    {
        if ($this->getReflected()->hasMethod($this->_method)) {
            $this->prependToMethod($source, $this->codeToModify());
        } else {
            $source .= $this->defaultSource();
        }
    }

    /**
     * Retrieves the reflection object
     * 
     * @return \PHP\Expando\Reflection\ReflectionClass
     */
    public function getReflected()
    {
        if (!isset($this->_reflected)) {
            $this->_reflected = new ReflectionClass($this->_path);
        }
        return $this->_reflected;
    }
    
    /**
     * Sets the reflected object
     * 
     * @param \PHP\Expando\Reflection\ReflectionClass $reflected
     * 
     * @return \PHP\Expando\Modifier\Proxy
     */
    public function setReflected(ReflectionClass $reflected)
    {
        $this->_reflected = $reflected;
        return $this;
    }
    
    /**
     * Prepends code to an existing method
     * 
     * @param string $source
     * @param string $codeToPrepent
     */
    protected function prependToMethod(&$source, $codeToPrepent)
    {
        $method = $this->getReflected()->getMethod($this->_method);
        $body = $method->getBody();
        $codeToPrepent .= $body;
        $source = \str_replace($body, $codeToPrepent, $source);
    }
    
}