<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Lorenzo Alberton <l dot alberton at quipo dot it>           |
// +----------------------------------------------------------------------+
//
// $Id: Translation2.php,v 1.1 2004/01/06 23:48:51 quipo Exp $
//
/**
 * @package Translation2
 * @version $Id: Translation2.php,v 1.1 2004/01/06 23:48:51 quipo Exp $
 */

/**
 * require PEAR base class
 */
require_once 'PEAR.php';

/**
 * Allows redefinition of alternate key for empty pageID
 */
if (!defined('TRANSLATION2_EMPTY_PAGEID_KEY')) {
    define('TRANSLATION2_EMPTY_PAGEID_KEY', 'array_key_4_empty_pageID');
}

/**
 * Class Error codes
 */
define('TRANSLATION2_ERROR_METHOD_NOT_SUPPORTED', -1);

/**
 * Translation2 class
 *
 */
class Translation2
{
    // {{{ class vars

    /**
     * Storage object
     * @var object
     */
    var $storage = '';

    /**
     * Class options
     * @var array
     */
    var $options = array();

    /**
     * Translated strings array
     * Used for cache purposes
     * @var array
     */
    var $data = array();

    /**
     * Default lang
     * @var array
     */
    var $lang = array();

    /**
     * Current pageID
     * @var string
     */
    var $currentPageID = null;

    /**
     * Fallback lang
     * @var array
     */
    var $langFallback = array();

    /**
     * String parameters
     * @var array
     */
    var $params = array();

    // }}}
    // {{{ Constructor

    /**
     * Constructor
     *
     * @param string $storageDriver Type of the storage driver
     * @param mixed  $options Additional options for the storage driver
     *                        (example: if you are using DB as the storage
     *                        driver, you have to pass the dsn string here)
     * @param array $params
     */
    function Translation2($storageDriver, $options='', $params)
    {
        if (is_object($storageDriver)) {
            $this->storage =& $storageDriver;
        } else {
            $this->storage = $this->_factory($storageDriver, $options);
        }
        $this->_setDefaultOptions();
        $this->_parseOptions($params);
        $this->storage->_parseOptions($params);
    }

    // }}}
    // {{{ _factory()

    /**
     * Return a storage driver based on $driver and $options
     *
     * @access private
     * @static
     * @param  string $driver  Type of storage class to return
     * @param  string $options Optional parameters for the storage class
     * @return object Object   Storage object
     */
    function _factory($driver, $options='')
    {
        $storage_path = 'Translation2'.DIRECTORY_SEPARATOR
                        .'Container'.DIRECTORY_SEPARATOR.strtolower($driver).'.php';
        $storage_class = 'Translation2_Container_'.strtolower($driver);
        require_once $storage_path;
        return new $storage_class($options);
    }

    // }}}
    // {{{ _setDefaultOptions()

    /**
     * Set some default options
     *
     * @access private
     * @return void
     */
    function _setDefaultOptions()
    {
        $this->options['ParameterPrefix']   = '&&';
        $this->options['ParameterPostfix']  = '&&';
        $this->options['ParameterAutoFree'] = true;
        $this->options['prefetch']          = true;
    }

    // }}}
    // {{{ _parseOptions()

    /**
     * Parse options passed to the base class
     *
     * @access private
     * @param  array
     */
    function _parseOptions($array)
    {
        foreach ($array as $key => $value) {
            if (isset($this->options[$key])) {
                $this->options[$key] = $value;
            }
        }
    }

    // }}}
    // {{{ setLang()

    /**
     * Set default lang
     * @param string $langID
     */
    function setLang($langID)
    {
        $this->lang = $this->storage->setLang($langID);
        //prepare cache container
        if (!array_key_exists($langID, $this->data)) {
            $this->data[$langID] = array();
        }
    }

    // }}}
    // {{{ setLangFallback()

    /**
     * Set default fallback lang
     *
     * When a string in the default lang is empty,
     * fetch the string in the fallback language
     * @param string $langID
     */
    function setLangFallback($langID=null)
    {
        if (empty($langID)) {
            //unset fallback language
            $this->langFallback = array();
        } else {
            $this->langFallback = $this->storage->getLangData($langID);
            //prepare cache container
            if (!array_key_exists($langID, $this->data)) {
                $this->data[$langID] = array();
            }
        }
    }

    // }}}
    // {{{ setPageID($pageID)

    /**
     * Set default page
     * Prefetch strings for this page
     * @param string $langID
     */
    function setPageID($pageID=null)
    {
        $this->currentPageID = $pageID;
        if ($this->options['prefetch']) {
            $key = empty($pageID) ? TRANSLATION2_EMPTY_PAGEID_KEY : $pageID;
            $this->data[$this->lang['id']][$key] = $this->storage->getPage($pageID);
        }
    }

    // }}}
    // {{{ getLang()

    /**
     * get lang info
     * @param string $langID
     * @param string $format ['name', 'meta', 'error_text', 'array']
     * @return mixed [string | array], depending on $format
     */
    function getLang($langID=null, $format='name')
    {
        if (is_null($langID) || ($langID == $this->lang['id'])) {
            $lang = $this->lang;
        } else {
            $lang = $this->storage->getLangData($langID);
        }

        switch ($format) {
            case 'name':
            case 'meta':
            case 'error_text':
                    return $lang[$format];
                    break;
            case 'array':
                    return $lang;
                    break;
            default:
                    return $lang['name'];
        }
    }

    // }}}
    // {{{ getLangs()

    /**
     * get langs
     * @param string $format ['ids', 'names', 'array']
     * @return array
     */
    function getLangs($format='name')
    {
        return $this->storage->getLangs($format);
    }

    // }}}
    // {{{ setParams()

    /**
     * Set parameters for next string
     * @param params $params
     */
    function setParams($params=null)
    {
        if (empty($params)) {
            $this->params = array();
        } elseif (is_array($params)) {
            $this->params = $params;
        } else {
            $this->params = array($params);
        }
    }

    // }}}
    // {{{ get()

    /**
     * Get translated string
     *
     * First check if the string is cached, if not => fetch the page
     * from the container and cache it for later use.
     * If the string is empty, check the fallback language; if
     * the latter is empty too, then return the $defaultText.
     *
     * @param string $stringID
     * @param string $pageID
     * @param string $langID
     * @param string $defaultText Text to display when the strings in both
     *                            the default and the fallback lang are empty
     * @return string
     */
    function get($stringID, $pageID=null, $langID=null, $defaultText='')
    {
        if (is_null($pageID)) {
            $pageID = $this->currentPageID;
        }
        if ($this->options['prefetch']) {
            $this->getPage($pageID, $langID);
        }
        $pageID_key = empty($pageID) ? TRANSLATION2_EMPTY_PAGEID_KEY : $pageID;
        $langID_key = empty($langID) ? $this->lang['id'] : $langID;

        if (!array_key_exists($langID_key, $this->data) ||
            !array_key_exists($pageID_key, $this->data[$langID_key])
        ) {
            $str = $this->storage->getOne($stringID, $pageID, $langID);
        } else {
            if (!array_key_exists($stringID, $this->data[$langID_key][$pageID_key])) {
                $str = '';
            } else {
                $str = $this->data[$langID_key][$pageID_key][$stringID];
            }
        }

        if (empty($str)) {
            if ($langID != $this->langFallback['id']) {
                return $this->get($stringID, $pageID, $this->langFallback['id'], $defaultText);
            } else {
                $str = empty($defaultText) ? $this->lang['error_text'] : $defaultText;
            }
        }

        if (count($this->params)) {
            while (list($name, $value) = each($this->params)) {
			    $str = str_replace($this->options['ParameterPrefix']
			            	       . $name . $this->options['ParameterPostfix'],
			                       $value,
			                       $str);
			}
            if ($this->options['ParameterAutoFree']) {
                $this->params = array();
            }
        }
        return $str;
    }

    // }}}
    // {{{ getPage()

    /**
     * Get the array of strings in a page
     *
     * First check if the strings are cached, if not => fetch the page
     * from the container and cache it for later use.
     *
     * @param string $pageID
     * @param string $langID
     * @return array
     */
    //function getPage($pageID=null, $langID=null, $tryFallback=false, $defaultText='') <= WHAT ABOUT THIS?
    function getPage($pageID=null, $langID=null)
    {
        if (is_null($pageID)) {
            $pageID = $this->currentPageID;
        }
        $pageID_key = empty($pageID) ? TRANSLATION2_EMPTY_PAGEID_KEY : $pageID;
        $langID_key = empty($langID) ? $this->lang['id'] : $langID;

        $notDefaultLang = (!is_null($langID) && ($langID != $this->lang['id'])) ? true : false;
        if ($notDefaultLang) {
            $bkp_lang = $this->lang['id'];
            $this->setLang($langID);
        }
        if (!array_key_exists($pageID_key, $this->data[$langID_key])) {
            $this->data[$langID_key][$pageID_key] = $this->storage->getPage($pageID);
        }
        if ($notDefaultLang) {
            $this->setLang($bkp_lang);
        }
        return $this->data[$langID_key][$pageID_key];
    }

    // }}}
    // {{{ translate()

    /**
     * Get translated string
     *
     * @param string $string This is NOT the stringID, this is a real string.
     *               The method will search for its matching stringID, and then
     *               it will return the associate string in the selected language.
     * @param string $langID
     * @return string
     */
    function translate($string, $langID)
    {
        //is a search in the cache (before the db query) worth it?
        $stringID = $this->storage->getStringID($string);
        if (PEAR::isError($stringID) || empty($stringID)) {
            return $this->lang['error_text'];
        }
        return $this->get($stringID, null, $langID); //$pageID IS NOT USED. IS IT OK WITH EVERYONE?
    }

    // }}}
}
?>