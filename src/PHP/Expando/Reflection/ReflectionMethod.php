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
 * @subpackage Reflection
 * @author     Marcello Duarte <marcello.duarte@gmail.com>
 * @copyright  2011 Marcello Duarte <marcello.duarte@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.github.com/MarcelloDuarte/php-expando
 * @since      File available since Release 0.1.0
 */
namespace PHP\Expando\Reflection;

use \PHP\Expando\Reflection\Util\MethodTokenizer;

/**
 * ReflectionMethod
 * 
 * Light weight replacement for \ReflectionMethod with added methods
 * 
 * @package Metaclass
 * @author  Marcello Duarte <marcello.duarte@gmail.com>
 */
class ReflectionMethod
{
    /**
     * Name of the method
     * 
     * @var unknown_type
     */
    protected $_name;
    
    /**
     * Path to the class of which the method is a part
     * 
     * @var string
     */
    protected $_path;
    
    /**
     * ReflectionMethod is constructed with name and path
     * 
     * @param string $name
     * @param string $path
     */
    public function __construct($name, $path)
    {
        $this->_name = $name;
        $this->_path = $path;
        $this->extractBody();
    }
    
    /**
     * Gets the method body
     * 
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * Extracts the method body from source
     */
    private function extractBody()
    {
        $string = $this->asString();
        $body = \substr($string, \strpos($string, '{'));
        $this->body = \trim(\rtrim(\rtrim($body), ';'), '{}');
    }
    
    /**
     * Extracts the full method definition
     * 
     * @return string
     */
    public function asString()
    {
        $tokenizer = new MethodTokenizer($this->getSource(), $this->_name);
        return $tokenizer->extract();
    }
    
    /**
     * Retrieves the source of the class from the file
     * 
     * @return string
     */
    public function getSource()
    {
        return rtrim(trim(\file_get_contents($this->_path)), '}');
    }
}
