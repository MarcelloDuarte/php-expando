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
namespace PHP\Expando;

/**
 * @see \PHP\Expando\Modifier\ClassMetaclassProxy
 */
use \PHP\Expando\Modifier\ClassMetaclassProxy;

/**
 * @see \PHP\Expando\Modifier\ObjectMetaclassProxy
 */
use \PHP\Expando\Modifier\ObjectMetaclassProxy;

/**
 * @see \PHP\Expando\Modifier\Interceptor
 */
use \PHP\Expando\Modifier\Interceptor;

/**
 * @see \PHP\Expando\Modifier\MetaclassProperties
 */
use \PHP\Expando\Modifier\MetaclassProperties;

/**
 * @see \PHP\Expando\Modifier\ClosingBraces
 */
use \PHP\Expando\Modifier\ClosingBraces;

/**
 * Parser
 * 
 * Responsible to parse a class according to modifiers passed to it.
 * 
 * @package Parser
 * @author  Marcello Duarte <marcello.duarte@gmail.com>
 */
class Parser
{
    /**
     * Modifiers added to the parser
     * 
     * @var array<\PHP\Expando\Modifier>
     */
    protected $_modifiers = array();
    
    /**
     * The code to be parsed
     * 
     * @var string
     */
    protected $_source;
    
    /**
     * Path to the class in the file system
     * 
     * @var string
     */
    protected $_path;
    
    /**
     * The parser is constructed with the path to the class
     * 
     * @param string $path
     */
    public function __construct($path)
    {
        $this->_path = $path;
    }
    
    /**
     * Parsers the source. Command pattern
     * 
     * @return string
     */
    public function parse()
    {
        $this->loadSource();
        
        foreach ($this->_modifiers as $modifier) {
            $modifier->modify($this->_source);
        }
        
        return $this->_source;
    }
    
    /**
     * Adds a modifier to the modifier queue
     * 
     * @param \PHP\Expando\Modifier $modifier
     * 
     * @return \PHP\Expando\Parser
     */
    public function addModifier($modifier)
    {
        $modifier = "\\PHP\\Expando\\Modifier\\$modifier";
        $this->_modifiers[] = new $modifier($this->_path);
        return $this;
    }
    
    /**
     * Loads the source code
     */
    private function loadSource()
    {
        $source = rtrim(trim(\file_get_contents($this->_path)), '}');
        $this->_source = \preg_replace('/\<\?php/', '', $source, 1);
    }
    
}