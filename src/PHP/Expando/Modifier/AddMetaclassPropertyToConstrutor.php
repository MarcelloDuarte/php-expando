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
 * @see \PHP\Expando\Modifier
 */
use \PHP\Expando\Modifier;

/**
 * @see \PHP\Expando\Modifier\Proxy
 */
use \PHP\Expando\Modifier\Proxy;

/**
 * AddMetaclassPropertyToConstrutor
 * 
 * This modifier does what is named after: adds a metaclass property straight
 * into the constructor, initializing it with a \PHP\Expando\Metaclass object
 * 
 * @package Metaclass
 * @author  Marcello Duarte <marcello.duarte@gmail.com>
 */
class AddMetaclassPropertyToConstrutor extends Proxy implements Modifier
{
    /**
     * The method to mofify
     * 
     * @var string
     */
    protected $_method = '__construct';
    
    /**
     * Code to add to the constructor
     * 
     * @return string
     */
    protected function codeToModify()
    {
        return '
        $this->metaclass = new \PHP\Expando\Metaclass($this);
    ';
    }
    
    /**
     * Empty constructor in case class doesn't have any to modify
     * 
     * @return string
     */
    protected function defaultSource()
    {
        return \sprintf(
            '    public function __construct() { %s }', $this->codeToModify()
        );
    }
}