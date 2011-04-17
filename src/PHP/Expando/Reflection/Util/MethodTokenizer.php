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
namespace PHP\Expando\Reflection\Util;

class MethodTokenizer
{
    /**
     * Tokens that indicates we are inside a method
     * 
     * @var array
     */
    protected $_methodTokens = array(
        'T_FINAL', 'T_FUNCTION', 'T_PUBLIC',
        'T_PRIVATE', 'T_PROTECTED', 'T_STATIC'
    );
    
    /**
     * Index of the braces
     * 
     * @var integer
     */
    protected $_i = 0;
    
    /**
     * Code collected
     * 
     * @var array
     */
    protected $_code = array();
    
    /**
     * Whether current token is a function token
     * 
     * @var boolean
     */
    protected $_isFunctionToken = false;
    
    /**
     * Whether current method is the method I am after
     * 
     * @var boolean
     */
    protected $_isMyMethod = false;
    
    /**
     * Whether we are at the closing index
     * 
     * @var boolean
     */
    protected $_closingIndex = false;
    
    /**
     * Whether we are inside a method
     * 
     * @var boolean
     */
    protected $_insideMethod = false;
    
    /**
     * Method Tokenizer is created with source and method name
     * 
     * @param string $source
     * @param string $name
     */
    public function __construct($source, $name)
    {
        $this->_source = $source;
        $this->_name = $name;
    }
    
    /**
     * Gets all tokens
     * 
     * @return array
     */
    public function getTokens()
    {
        return token_get_all($this->_source);
    }
    
    /**
     * Extract the method from the tokens
     * 
     * @return string
     */
    public function extract()
    {
        $result = array();
        
        foreach ($this->getTokens() as $token) {
            
            if ($this->notYetInsideAMethod()) {
                if ($this->enteringAMethod($token)) {
                    $this->startCopyingCode($token);
                }
            } else {
                if ($this->justEnteredMyMethod($token)) {
                    $result = $this->startCollectingResult();
                    continue;
                }
                if ($this->stillMyMethod()) {
                    $result[] = is_array($token) ? $token[1] : $token;
                } else {
                    $this->_code[] = is_array($token) ? $token[1] : $token;
                }
                if ($this->aboutToEnterAMethod($token)) {
                    $this->_isFunctionToken = true;
                }
                if ($this->atOpeningBraces($token)) {
                    $this->_closingIndex = $this->getClosingBracesIndex(
                        &$tokens, $this->_i
                    );
                }
                if ($this->reachedTheEndOfMyMethod()) {
                    $result[] = $token;
                    $this->flush();
                }
            }
            
            $this->_i++;
        }
        
        return implode('', $result);
    }
    
    /**
     * Gets the closing braces index based on the opening token index
     * 
     * @param array   $tokens
     * @param integer $openingTokenIndex
     * 
     * @return boolean|integer
     */
    private function getClosingBracesIndex(&$tokens, $openingTokenIndex)
    {     
        $i = $openingTokenIndex;
        $openBrace = 1;
        $closeBrace = 0;
        $count = count($tokens);
        
        if ($this->notAtOpeningBraces($tokens[$i])) {
            return false;
        }
        
        while ($openBrace !== $closeBrace) {
            $i++;
            
            if ($i >= $count) {
                return false;
            }
            
            if ($this->openingBraces($tokens[$i])) {
                $openBrace++;
            }
            
            if ($tokens[$i] === '}') {
                $closeBrace++;
            }
        } 
        return $i; 
    }
    
    /**
     * Starts copying code
     */
    private function startCopyingCode($token)
    {
        $this->_insideMethod = true;
        $this->_code[] = $token[1];
    }

    
    /**
     * Decides whether we are inside a method yet
     * 
     * @return boolean
     */
    private function notYetInsideAMethod()
    {
        return $this->_insideMethod === false;
    }
    
    /**
     * Whether we have just entered a method
     * 
     * @param array|string $token
     * @return boolean
     */
    private function enteringAMethod($token)
    {
        return is_array($token) &&
               in_array(token_name((int)$token[0]), $this->_methodTokens);
    }
    
    /**
     * Whether we have just entered MY method
     * 
     * @param array|string $token
     * @return boolean
     */
    private function justEnteredMyMethod($token)
    {
        return $this->_isFunctionToken &&
               is_array($token) &&
               $token[1] === $this->_name;
    }
    
    /**
     * Whether I am about to enter a method 
     *
     * @param array|string $token
     * @return boolean
     */
    private function aboutToEnterAMethod($token)
    {
        return is_array($token) && $token[1] === 'function';
    }
    
    /**
     * Whether I am at a opening brace token
     * 
     * @param string|array $token
     * @return boolean
     */
    private function atOpeningBraces($token)
    {
        return !is_array($token) && $this->_insideMethod && $token === '{';
    }
    
    /**
     * Starts colecting the code into the result
     * 
     * @return string
     */
    private function startCollectingResult()
    {
        $this->_isMyMethod = true;
        return $this->_code;
    }
    
    /**
     * Whether I've reached the end of my method
     * 
     * @return boolean
     */
    private function reachedTheEndOfMyMethod()
    {
        return $this->_closingIndex === $this->_i;
    }
    
    /**
     * Reset the main varibles
     */
    private function flush()
    {
        $this->_code = array();
        $this->_insideMethod =
        $this->_isFunctionToken =
        $this->_isMyMethod = false;
    }
    
    /**
     * Whether I am still in my method
     * 
     * @return integer
     */
    private function stillMyMethod()
    {
        return $this->_isMyMethod;
    }
    
    /**
     * Whether I am not at an opening braces token
     * 
     * @param array|string $token
     * @return boolean
     */
    private function notAtOpeningBraces($token)
    {
        return $token !== '{' ||
            (is_array($token) && isset ($token[0]) &&
             $token[0] !== T_CURLY_OPEN &&
             $token[0] !== T_DOLLAR_OPEN_CURLY_BRACES);
    }
    
    /**
     * Whether we are at an opening brace token
     * 
     * @param array|string $token
     * @return boolean
     */
    private function openingBraces($token)
    {
        return $token === '{' ||
            (is_array($token) && isset ($token[0]) &&
             ($token[0] == T_CURLY_OPEN ||
              $token[0] == T_DOLLAR_OPEN_CURLY_BRACES));
    }
}