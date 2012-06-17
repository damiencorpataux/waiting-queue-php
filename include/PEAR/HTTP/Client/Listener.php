<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at                              |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Alexey Borzov <avb@php.net>                                  |
// +----------------------------------------------------------------------+
//
// $Id: Listener.php,v 1.2 2003/12/12 15:53:21 avb Exp $

require_once 'HTTP/Request/Listener.php';

/**
 * The class is DEPRECATED, use HTTP_Request_Listener instead.
 * 
 * This class implements the Observer part of a Subject-Observer
 * design pattern. It listens to the events sent by a 
 * HTTP_Client instance.
 *
 * @deprecated
 * @package HTTP_Client
 * @author  Alexey Borzov <avb@php.net>
 * @version $Revision: 1.2 $
 */
class HTTP_Client_Listener extends HTTP_Request_Listener
{
}
?>
