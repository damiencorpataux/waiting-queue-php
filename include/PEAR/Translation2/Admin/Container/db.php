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
// | Author: Lorenzo Alberton <l dot alberton at quipo dot it>            |
// +----------------------------------------------------------------------+
//
// $Id: db.php,v 1.1 2004/01/06 23:48:50 quipo Exp $
//

require_once 'Translation2'.DIRECTORY_SEPARATOR.'Container'.DIRECTORY_SEPARATOR.'db.php';

/**
 * Storage driver for storing/fetching data to/from a database
 *
 * This storage driver can use all databases which are supported
 * by the PEAR DB abstraction layer to fetch data.
 *
 * @package  Translation2
 * @version  $Revision: 1.1 $
 */
class Translation2_Admin_Container_db extends Translation2_Container_db
{

    // {{{ class vars


    // }}}
    // {{{ createNewLang()

    /**
     * Creates a new table to store the strings in this language.
     * If the table is shared with other langs, it is ALTERed to
     * hold strings in this lang too.
     *
     * @param string $langID
     * @return mixed true on success, PEAR_Error on failure
     */
    function createNewLang($langID)
    {
        $res = $this->query('SHOW TABLES', 'getAll');
        if (PEAR::isError($res)) {
            return $res;
        }
        if (empty($res) || !is_array($res)) {
            //return error
        }

        $lang_col = str_replace('%s', $langID, $this->options['string_text_col']);
        if (empty($lang_col)) {
            $lang_col = $this->currentLang['id'];
        }

        if (in_array($this->options['strings_tables'][$langID], $res)) {
            //table exists
            $query = sprintf('ALTER TABLE %s ADD COLUMN %s',
                            $this->options['strings_tables'][$langID],
                            $lang_col
            );
        } else {
            //is this query portable??
            $query = sprintf('CREATE TABLE %s ( '
                            .'%s CHAR(16) default NULL, '
                            .'%s CHAR(32) NOT NULL, '
                            .'%s TEXT, '
                            .'UNIQUE KEY tablekey (%s, %s), '
                            .'KEY page_id (%s), '
                            .'KEY string_id (%s))',
                            $this->options['strings_tables'][$langID],
                            $this->options['string_page_id_col'],
                            $this->options['string_id_col'],
                            $lang_col,
                            $this->options['string_page_id_col'],
                            $this->options['string_id_col'],
                            $this->options['string_page_id_col'],
                            $this->options['string_id_col']
            );
        }
        return $this->query($query);
    }

    // }}}
    // {{{ addLangToAvailList()

    /**
     * Creates a new entry in the langsAvail table.
     * If the table doesn't exist yet, it is created.
     *
     * @param array $langData array('lang_id'    => 'en',
     *                              'table_name' => 'i18n',
     *                              'name'       => 'english',
     *                              'meta'       => 'some meta info',
     *                              'error_text' => 'not available');
     * @return mixed true on success, PEAR_Error on failure
     */
    function addLangToAvailList($langData)
    {
        $res = $this->query('SHOW TABLES', 'getAll');
        if (PEAR::isError($res)) {
            return $res;
        }
        if (empty($res) || !is_array($res)) {
            //return error
        }

        if (!in_array($this->options['langs_avail_table'], $res)) {
            //is this query portable??
            $query = sprintf('CREATE TABLE %s ('
                            .'%s CHAR(16), '
                            .'%s CHAR(200), '
                            .'%s TEXT, '
                            .'%s CHAR(250), '
                            .'UNIQUE KEY (%s))',
                            $this->options['langs_avail_table'],
                            $this->options['lang_id_col'],
                            $this->options['lang_name_col'],
                            $this->options['lang_meta_col'],
                            $this->options['lang_errmsg_col'],
                            $this->options['lang_id_col']
            );
            $res = $this->query($query);
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        $query = sprintf('INSERT INTO %s (%s, %s, %s, %s) VALUES (%s, %s, %s, %s)',
	                $this->options['langs_avail_table'],
                    $this->options['lang_id_col'],
                    $this->options['lang_name_col'],
                    $this->options['lang_meta_col'],
                    $this->options['lang_errmsg_col'],
                    $this->db->quote($langData['lang_id']),
                    $this->db->quote($langData['name']),
                    $this->db->quote($langData['meta']),
                    $this->db->quote($langData['error_text'])
        );

        return $this->query($query);
    }

    // }}}
    // {{{ add()

    /**
     * Add a new entry in the strings table.
     *
     * @param string $stringID
     * @param string $pageID
     * @param array  $stringArray Associative array with string translations.
     *               Sample format:  array('en' => 'sample', 'it' => 'esempio')
     * @return mixed true on success, PEAR_Error on failure
     */
    function add($stringID, $pageID, $stringArray)
    {
        $langs = array_keys($stringArray);
        $numLangs = count($langs);
        $availableLangs = $this->getLangs('ids');
        foreach ($langs as $key => $langID) {
            if (!in_array($langID, $availableLangs)) {
                unset($langs[$key]);
            }
        }

        if (!count($langs)) {
            //return error: no valid lang provided
            return true;
        }

        //naive algorithm: if the strings table is the same for all languages,
        //then do one query only. NB: this will fail when *some* langs share the
        //same table, but not *all* of them do.
        $oneQuery = true;
        if ($numLangs > 1) {
            for ($i=1; $i<$numLangs; $i++) {
                if ($this->options['strings_tables'][$langs[$i]] !=
                    $this->options['strings_tables'][$langs[0]]
                ) {
                    $oneQuery = false;
                    break;
                }
            }
        }

        if ($oneQuery) {
            $what = array();
            $what[$this->options['string_id_col']] = $this->db->quote($stringID);
            $what[$this->options['string_page_id_col']] = (empty($pageID) ? 'NULL' : $this->db->quote($pageID));
            foreach ($langs as $langID) {
                $lang_col = str_replace('%s', $langID, $this->options['string_text_col']);
                $what[$lang_col] = $this->db->quote($stringArray[$langID]);
            }
            $query = 'INSERT INTO '. $this->options['strings_tables'][$langs[0]] .' ('
                    .implode(', ', array_keys($what)) .') VALUES ('
                    .implode(', ', $what) .')';
            $res = $this->query($query);
            if (PEAR::isError($res)) {
                return $res;
            }
        } else {
            foreach ($langs as $langID) {
                $lang_col = str_replace('%s', $langID, $this->options['string_text_col']);
                $query = 'INSERT INTO '. $this->options['strings_tables'][$langID] .' ('
                        .$lang_col .') VALUES ('. $this->db->quote($stringArray[$langID]).')';
                $res = $this->query($query);
                if (PEAR::isError($res)) {
                    return $res;
                }
            }
        }

       return true;
    }

    // }}}
    // {{{ remove()

    /**
     * Remove an entry from the strings table.
     *
     * @param string $stringID
     * @param string $pageID
     * @return mixed true on success, PEAR_Error on failure
     */
    function remove($stringID, $pageID)
    {
        $langs = $this->getLangs('ids');
        $tables = array();
        foreach ($langs as $langID) {
            $tables[] = $this->options['strings_tables'][$langID];
        }
        $tables = array_unique($tables);
        foreach ($tables as $table) {
            $query = 'DELETE FROM '.$table.' WHERE ';
            $where = array();
            $where[] = $this->options['string_id_col'] .'='. $this->db->quote($stringID);
            $where[] = $this->options['string_page_id_col']
                       . (empty($pageID) ? ' IS NULL' : '='. $this->db->quote($pageID));
            $query .= implode(' AND ', $where);
            $res = $this->query($query);
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        return true;
    }

    // }}}
}
?>