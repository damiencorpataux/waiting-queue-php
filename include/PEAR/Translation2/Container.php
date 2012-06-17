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
// $Id: Container.php,v 1.1 2004/01/06 23:48:51 quipo Exp $
//

/**
 * Storage class
 *
 * @package  Translation2
 */
class Translation2_Container
{
    // {{{ Class vars

    /**
     * @var array
     * @access private
     */
    var $currentLang = array();

    /**
     * @var array
     * @access private
     */
    var $langs = array();

    // }}}
    // {{{ Constructor

    /**
     * Constructor
     * Has to be overwritten by each storage class
     * @access public
     */
    function Translation2_Container()
    {
    }

    // }}}
    // {{{ _parseOptions()

    /**
    * Parse options passed to the container class
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
    // {{{ fetchData()

    /**
     * Fetch data from storage container
     *
     * Has to be overwritten by each storage class
     *
     * @access public
     */
    function fetchData()
    {
    }

    // }}}
    // {{{ setLang()

    /**
     * Sets the current lang
     *
     * @param  string $langID
     */
    function setLang($langID)
    {
        $this->getLangs(); //load available languages, if not loaded yet (ignore return value)
        $this->currentLang = $this->langs[$langID];
        return $this->langs[$langID];
    }

    // }}}
    // {{{ getLang()

    /**
     * Gets the current lang
     * @param string $format
     * @return mixed array with current lang data or null if not set yet
     */
    function getLang($format='id')
    {
        return isset($this->currentLang['id']) ? $this->currentLang : null;
    }

    // }}}
    // {{{ getLangData()

    /**
     * Gets the array data for the lang
     * @param  string $langID
     * @param string $format
     * @return mixed array with lang data or null if not available
     */
    function getLangData($langID, $format='id')
    {
        $langs = $this->getLangs('array');
        return isset($langs[$langID]) ? $langs[$langID] : null;
    }

    // }}}
    // {{{ getLangs()

    /**
     * Gets the available languages
     * @param string $format ['array' | 'ids' | 'names']
     */
    function getLangs($format='array')
    {
        //if not cached yet, fetch langs data from the container
        if (empty($this->langs) || !count($this->langs)) {
            $this->fetchLangs(); //container-specific method
        }

        $tmp = array();
        switch ($format) {
            case 'array':
                    foreach ($this->langs as $aLang) {
                        $tmp[$aLang['id']] = $aLang;
                    }
                    break;
            case 'ids':
                    foreach ($this->langs as $aLang) {
                        $tmp[] = $aLang['id'];
                    }
                    break;
            case 'names':
            default:
                    foreach ($this->langs as $aLang) {
                        $tmp[$aLang['id']] = $aLang['name'];
                    }
        }
        return $tmp;
    }

    // }}}
    // {{{ fetchLangs()

    /**
     * Fetch the available langs if they're not cached yet.
     * Containers should implement this method.
     */
    function fetchLangs()
    {
        return $this->raiseError(TRANSLATION_ERROR_METHOD_NOT_SUPPORTED);
    }

    // }}}
    // {{{ getPage()

    /**
     * Returns an array of the strings in the selected page
     * Containers should implement this method.
     * @param string $pageID
     * @return array
     */
    function getPage($pageID)
    {
        return $this->raiseError(TRANSLATION_ERROR_METHOD_NOT_SUPPORTED);
    }

    // }}}
    // {{{ getOne()

    /**
     * Get a single item from the container, without caching the whole page
     * Containers should implement this method.
     */
    function getOne($stringID, $pageID=null, $langID=null)
    {
        return $this->raiseError(TRANSLATION_ERROR_METHOD_NOT_SUPPORTED);
    }

    // }}}
}
?>
