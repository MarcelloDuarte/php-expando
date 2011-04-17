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
 * @subpackage Loader
 * @author     Marcello Duarte <marcello.duarte@gmail.com>
 * @copyright  2011 Marcello Duarte <marcello.duarte@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.github.com/MarcelloDuarte/php-expando
 * @since      File available since Release 0.1.0
 */
namespace PHP\Expando;

/**
 * @see \PHP\Expando\Reflection\ReflectionClass
 */
use \PHP\Expando\Reflection\ReflectionClass;

/**
 * Loader
 * 
 * Responsible to add proxies and interceptors to all classes loaded via
 * PHP_Expando
 * 
 * @package Metaclass
 * @author  Marcello Duarte <marcello.duarte@gmail.com>
 */
class Loader
{
    /**
     * Class source code
     * 
     * @var string
     */
    protected $_source = '';
    
    /**
     * Class to be loaded
     * 
     * @var string
     */
    protected $_class;
    
    /**
     * Loader is constructed with the class to load
     * 
     * @param string $class
     */
    public function __construct($class)
    {
        $this->_class = $class;
        $this->_path = $this->extractPath();
    }

    /**
     * Loads a class, converts the static call, creating a loader object.
     * To use PHP_Expando just register this method with an autoloader, or
     * use {@link \PHP\Expando\Loader::register()}
     * 
     * @param string $class
     * 
     * @return boolean
     * @throws \RuntimeException
     */
    public static function load($class)
    {
        $loader = new self($class);
        return $loader->doLoad();
    }

    /**
     * Checks if it is a PHP_Expando class, includes it normally, otherwise
     * will add proxies and interceptors to class
     * 
     * @return boolean
     */
    public function doLoad()
    {
        if ($this->isAnExpandoClass()) {
            $this->includeExpandoClass();
            return true;
        }

        $this->addProxiesAndInterceptors();
        return $this->loadClass();
    }
    
    /**
     * Checks to see if this is a class from within the PHP_Expando Package
     * 
     * @return boolean
     */
    private function isAnExpandoClass()
    {
        return strpos(ltrim($this->_class, '\\'), 'PHP\Expando') === 0;
    }

    /**
     * Includes a PHP_Expando class
     */
    private function includeExpandoClass()
    {
        $ds = \DIRECTORY_SEPARATOR;
        $file = str_replace('\\', $ds, $this->_class);
        include_once (
            dirname(dirname(dirname(__FILE__))) . $ds . "$file.php"
        );
    }
    
    /**
     * Adds proxies and interceptors to the parser
     */
    private function addProxiesAndInterceptors()
    {
        $this->_parser = $this->getParser();
    
        $this->_parser->addModifier('MetaclassProperties')
                      ->addModifier('AddMetaclassPropertyToConstrutor')
                      ->addModifier('MetaclassPropertyProxy')
                      ->addModifier('Interceptor')
                      ->addModifier('ClosingBraces');
    }
    
    /**
     * Retrieves the parser. Inline factory
     * 
     * @return \PHP\Expando\Parser;
     */
    public function getParser()
    {
        if (!isset($this->_parser)) {
            $this->_parser = new Parser($this->_path);
        }
        return $this->_parser;
    }
    
    /**
     * Sets the parser. Allows for injection
     * 
     * @param Parser $parser
     */
    public function setParser(Parser $parser)
    {
        $this->_parser = $parser;
    }

    /**
     * Registers this loader with
     * {@link http://uk.php.net/spl-autoload-register spl_autoload_register}
     */
    public static function register()
    {
        \spl_autoload_register(array(__CLASS__, 'load'));
    }

    /**
     * Unregisters this loader
     * {@link http://uk.php.net/spl-autoload-unregister spl_autoload_unregister}
     */
    public static function unregister()
    {
        \spl_autoload_unregister(array(__CLASS__, 'load'));
    }  

    /**
     * Extracts the path of the file for a class in file system
     * 
     * @param string $class
     * 
     * @return string|boolean
     */
    public function extractPath()
    {
        $file = \str_replace('\\', \DIRECTORY_SEPARATOR, $this->_class);
        $file = \str_replace('_', \DIRECTORY_SEPARATOR, $file) . ".php";

        $paths = \explode(\PATH_SEPARATOR, \get_include_path());

        foreach ($paths as $path) {
            $fullPath = $path . \DIRECTORY_SEPARATOR . $file;
            if (\file_exists($fullPath)) {
                return $fullPath;
            }
        }
        
        throw new \RuntimeException(
            "{$this->_class} not found in " . \get_include_path()
        );
    }
    
    /**
     * Evaluates class code
     * 
     * @param string $class
     * 
     * @return boolean
     */
    private function loadClass()
    {
        eval($this->_parser->parse());
        eval(
           $this->_class .
           "::\$metaclass = new \PHP\Expando\Metaclass\ClassScope(
               '{$this->_class}'
           );"
        );
        return true;
    }
}
