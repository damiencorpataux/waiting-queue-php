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
// $Id: Admin.php,v 1.1 2004/01/06 23:48:49 quipo Exp $
//
/**
 * @package Translation2
 * @version $Id: Admin.php,v 1.1 2004/01/06 23:48:49 quipo Exp $
 */

/**
 * require Translation2 base class
 */
require_once 'Translation2'.DIRECTORY_SEPARATOR.'Translation2.php';

/**
 * Administration utilities for translation string management
 *
 * @package  Translation2
 * @version  $Revision: 1.1 $
 */
class Translation2_Admin extends Translation2
{

    // {{{ class vars


    // }}}
    // {{{ _factory()

    /**
     * Return a storage driver based on $driver and $options
     *
     * Override Translation2::_factory()
     *
     * @access private
     * @static
     * @param  string $driver  Type of storage class to return
     * @param  string $options Optional parameters for the storage class
     * @return object Object   Storage object
     */
    function _factory($driver, $options='')
    {
        $storage_path = 'Translation2'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR
                        .'Container'.DIRECTORY_SEPARATOR.strtolower($driver).'.php';
        $storage_class = 'Translation2_Admin_Container_'.strtolower($driver);
        require_once $storage_path;
        return new $storage_class($options);
    }

    // }}}
    // {{{ createNewLang

    /**
     * Prepare the storage container for a new lang.
     * If the langsAvail table doesn't exist yet, it is created.
     *
     * @param array $langData array('lang_id'    => 'en',
     *                              'table_name' => 'i18n',
     *                              'name'       => 'english',
     *                              'meta'       => 'some meta info',
     *                              'error_text' => 'not available');
     * @return mixed true on success, PEAR_Error on failure
     */
    function createNewLang($langData)
    {
        $res = $this->storage->createNewLang($langData['lang_id']);
        if (PEAR::isError($res)) {
            return $res;
        }
        $res = $this->storage->addLangToAvailList($langData);
        if (PEAR::isError($res)) {
            return $res;
        }
        $this->options['strings_tables'][$langData['lang_id']] = $langData['table_name'];
        $this->storage->fetchLangs(); //update local cache
        return true;
    }

    // }}}
    // {{{ removeLang

    /**
     * Remove the lang from the langsAvail table and drop the strings table.
     * If the strings table holds other langs and $force==false, then
     * only the lang column is dropped. If $force==true the whole
     * table is dropped without any check
     *
     * @param string  $langID
     * @param boolean $force
     * @return mixed true on success, PEAR_Error on failure
     */
    function removeLang($langID=null, $force=false)
    {
        if (is_null($langID)) {
            //return error
        }
        $res = $this->storage->remove($langID, $force);
        if (PEAR::isError($res)) {
            return $res;
        }
        unset($this->storage->langs[$langID]);
        return true;
    }

    // }}}
    // {{{ add

    /**
     * add a new translation
     *
     * @param string $stringID
     * @param string $pageID
     * @param array  $stringArray Associative array with string translations.
     *               Sample format:  array('en' => 'sample', 'it' => 'esempio')
     * @return mixed true on success, PEAR_Error on failure
     */
    function add($stringID, $pageID=null, $stringArray)
    {
        return $this->storage->add($stringID, $pageID, $stringArray);
    }

    // }}}
    // {{{ remove

    /**
     * remove a translated string
     *
     * @param string $stringID
     * @param string $pageID
     * @return mixed true on success, PEAR_Error on failure
     */
    function remove($stringID, $pageID=null)
    {
        return $this->storage->remove($stringID, $pageID);
    }

    // }}}
    // {{{ ____remove  (backup code, just a proof of concept...)

    /**
     * remove a translated string
     *
     * @param string $stringID
     * @param string $pageID
     * @param array  $langs  leave empty if the string must be removed from all langs
     */
    function ____remove($stringID, $pageID=null, $langs=null)
    {
        if (is_null($langs)) {
            $langs = $this->getLangs('ids');
        } elseif (!is_array($langs)) {
            $langs = array($langs);
        }
        $this->storage->remove($stringID, $pageID, $langs);
    }

    // }}}
}
?>