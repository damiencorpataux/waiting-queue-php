<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author:  Wolfram Kriesing, Paolo Panto, vision:produktion <wk@visionp.de>
// +----------------------------------------------------------------------+
//
// $Id: EasyJoin.php,v 1.4 2003/06/17 18:04:52 cain Exp $
//

require_once 'DB/QueryTool/Query.php';

/**
*
*   @package    DB_QueryTool
*   @version    2002/09/03
*   @access     public
*   @author     Wolfram Kriesing <wolfram@kriesing.de>
*/
class DB_QueryTool_EasyJoin extends DB_QueryTool_Query
{

    /**
    *   this is the regular expression that shall be used to find a table's shortName
    *   in a column name, the string found by using this regular expression will be removed
    *   from the column name and it will be checked if it is a table name
    *   i.e. the default '/_id$/' would find the table name 'user' from the column name 'user_id'
    */
    var $_tableNamePreg = '/_id$/';

    /**
    *   this is to find the column name that is refered by it, so the default find
    *   from 'user_id' the column 'id' which will be used to refer to the 'user' table
    */
    var $_columnNamePreg = '/^.*_/';

    /**
    *   join the tables given, using the column names, to find out how to join the tables
    *   this is, if table1 has a column names table2_id this method will join
    *   WHERE table1.table2_id=table2.id
    *   all joins made here are only concatenated via AND
    */
    function autoJoin( $tables )
    {
// FIXXME if $tables is empty autoJoin all available tables that have a relation to $this->table, starting to search in $this->table
        settype($tables,'array');
        // add this->table to the tables array, so we go thru the current table first
        $tables = array_merge( array($this->table) , $tables );

        $shortNameIndexed = $this->getTableSpec( true , $tables );
        $nameIndexed = $this->getTableSpec( false , $tables );

//print_r($shortNameIndexed);
//print_r($tables);        print '<br><br>';
        if( sizeof($shortNameIndexed) != sizeof($tables) )
            $this->_errorLog("autoJoin-ERROR: not all the tables are in the tableSpec!<br>");

        $joinTables = array();
        $joinConditions = array();
        foreach( $tables as $aTable ) {             // go through $this->table and all the given tables
            if( $metadata = $this->metadata($aTable) )
            foreach ( $metadata as $aCol=>$x ) {   // go through each row to check which might be related to $aTable
                $possibleTableShortName = preg_replace( $this->_tableNamePreg, '' ,$aCol );
                $possibleColumnName = preg_replace( $this->_columnNamePreg, '' ,$aCol );
//print "$aTable.$aCol .... possibleTableShortName=$possibleTableShortName .... possibleColumnName=$possibleColumnName<br>";
                if (isset($shortNameIndexed[$possibleTableShortName])) {
                    // are the tables given in the tableSpec?
                    if (!$shortNameIndexed[$possibleTableShortName]['name'] ||
                        !$nameIndexed[$aTable]['name']) {
                        // its an error of the developer, so log the error, dont show it to the end user
                        $this->_errorLog("autoJoin-ERROR: '$aTable' is not given in the tableSpec!<br>");
                    } else {
                        // do only join different table.col combination, 
                        // we shoul not join stuff like 'question.question=question.question' this would be quite stupid, but it used to be :-(
                        if ($shortNameIndexed[$possibleTableShortName]['name'].$possibleColumnName!=$aTable.$aCol) {
                            $joinTables[] = $nameIndexed[$aTable]['name'];
                            $joinTables[] = $shortNameIndexed[$possibleTableShortName]['name'];
                            $joinConditions[] = $shortNameIndexed[$possibleTableShortName]['name'].".$possibleColumnName=$aTable.$aCol";
                        }
                    }
                }
            }
        }

        if (sizeof($joinTables) && sizeof($joinConditions)) {
            $joinTables = array_unique($joinTables);
            foreach( $joinTables as $key=>$val ) {
                if( $val == $this->table ) {
                    unset($joinTables[$key]);
                }
            }
//FIXXME set tables only when they are not already in the join!!!!!
                    
//print_r($joinTables); print '$this->addJoin('.implode(' AND ',$joinConditions).');<br>';
            $this->addJoin($joinTables,implode(' AND ',$joinConditions));
        }
//print '<br><br><br>';
    }

}
?>
