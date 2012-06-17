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
// $Id: QueryTool.php,v 1.1.1.1 2003/01/17 12:39:01 cain Exp $
//

require_once 'DB/QueryTool/EasyJoin.php';


/**
*   this class should be extended
*   This class is here to make it easy using the base 
*   class of the package by it's package name.
*   Since I tried to seperate the functionality a bit inside the
*   really working classes i decided to have this class here just to 
*   provide the name, since the functionality inside the other
*   classes might be restructured a bit but this name always stays.
*
*   @package    DB_QueryTool
*   @version    2002/04/02
*   @access     public
*   @author     Wolfram Kriesing <wk@visionp.de>
*/
class DB_QueryTool extends DB_QueryTool_EasyJoin
{
}

?>