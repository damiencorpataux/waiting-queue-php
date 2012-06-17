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
// $Id: Result.php,v 1.4 2003/02/26 18:19:50 cain Exp $
//

/**
*   this result actually contains the 'data' itself, the number of rows
*   returned and some additional info
*   using ZE2 you can also get retreive data from the result doing the following:
*   <vp_DB_Common-instance>->getAll()->getCount()
*   or
*   <vp_DB_Common-instance>->getAll()->getData()
*
*
*   @package    DB_QueryTool
*   @version    2002/07/11
*   @access     public
*   @author     Wolfram Kriesing <wolfram@kriesing.de>
*/
class DB_QueryTool_Result
{

    var $_data = array();
    var $_count = 0;
    // the counter for the methods getFirst, getNext
    var $_counter = null;

    /**
    *   create a new instance of result with the data returned by the query
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wolfram@kriesing.de>
    *   @param      array   the data returned by the result
    */
    function DB_QueryTool_Result( $data )
    {
        list($firstElement) = $data;
        if( is_array($firstElement) )               // is the array a collection of rows?
        {
            $this->_count = sizeof($data);
        }
        else
        {
            if( sizeof($data) > 0 )
                $this->_count = 1;
            else
                $this->_count = 0;
        }
        $this->_data = $data;
    }

    /**
    *   return the number of rows returned
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wolfram@kriesing.de>
    *   @param
    *   @return
    */
    function getCount()
    {
        return $this->_count;
    }

    /**
    *   get all the data returned
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wolfram@kriesing.de>
    *   @param
    *   @return
    */
    function getData( $key=null )
    {
        if( $key===null )
            return $this->_data;

        if( $this->_data[$key] )
            return $this->_data[$key];
        else
            return new PEAR_Error("there is no element with the key '$key'!");
    }

    /**
    *   get the first result set
    *   we are not using next, current, and reset, since those ignore keys
    *   which are empty or 0
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wolfram@kriesing.de>
    *   @param
    *   @return
    */
    function getFirst()
    {
        if( $this->getCount() > 0 )
        {
            $this->_dataKeys = array_keys($this->_data);
            $this->_counter = 0;
            return $this->_data[$this->_dataKeys[$this->_counter]];
        }
        return new PEAR_Error("there are no elements!");
    }

    function getNext()
    {
        if( $this->hasMore() )
        {
            $this->_counter++;
            return $this->_data[$this->_dataKeys[$this->_counter]];
        }
        return new PEAR_Error("there are no more elements!");
    }

    function hasMore()
    {
        if( $this->_counter+1 < $this->getCount() )
            return true;
        return false;
    }

    #TODO
    #function getPrevious()
    #function getLast()


}

?>
