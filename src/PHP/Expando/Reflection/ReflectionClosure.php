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

/**
 * ReflectionClosure
 * 
 * Adds some functionality to PHP's ReflectionFunction by enabling to extract
 * the body and signature of closures
 * 
 * @package Metaclass
 * @author  Marcello Duarte <marcello.duarte@gmail.com>
 */
class ReflectionClosure extends \ReflectionFunction
{
    /**
     * Closure's body
     * 
     * @var string
     */
    protected $_body;
    
    /**
     * Closure's signature
     * 
     * @var string
     */
    protected $_signature;

    /**
     * ReflectionClosure requires a \Closure to be constructed
     * 
     * @param \Closure $closure
     * 
     * @throws \ReflectionException
     */
    public function  __construct($closure)
    {
        if (!$closure instanceof \Closure) {
            throw new \ReflectionException('Not a closure');
        }
        parent::__construct($closure);

        $this->extractBody();
        $this->extractSignature();
    }

    /**
     * Gets the signature
     * 
     * @return string
     */
    public function getSignature()
    {
        return $this->_signature;
    }

    /**
     * Gets the body
     * 
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Returns the closure's declaration
     * 
     * @return string
     * @throws \ReflectionException
     */
    public function asString()
    {
        if (
            !\preg_match('/@@(.*)/', (string)$this, $pathAndLines) ||
            !isset($pathAndLines[1])
        ) {
            throw new \ReflectionException(
                'Could not extract path and lines from closure'
            );
        }

        $pathAndLines = \str_replace(' - ', ' ', \trim($pathAndLines[1]));
        list ($path, $start, $end) = \explode(' ', $pathAndLines);
        $closureAsArray = \array_slice(
            \file($path), $start - 1, $end - $start + 1
        );

        return \trim(implode('', $closureAsArray));
    }

    /**
     * Extracts the body from the closure declaration
     */
    private function extractBody()
    {
        $body = \substr($this->asString(), \strpos($this->asString(), '{'));
        $this->_body = \trim(\rtrim(\rtrim($body), ';'), '{}');
    }

    /**
     * Extracts the signature out of the definition
     */
    private function extractSignature()
    {
        $closureStart = '/([\$a-zA-Z0-9_\[\]\-\>]+)(\s*)(=)(\s*)(function)/i';
        $wholeMethod = preg_replace($closureStart, '$5', $this->asString(), 1);
        $noBody = \str_replace($this->_body, '', $wholeMethod);
        $noBody = rtrim(rtrim(rtrim(rtrim($noBody), '};')), '{');

        $this->_signature = $noBody;
    }
}
