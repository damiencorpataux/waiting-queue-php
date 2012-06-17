<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Alexey Borzov <avb@php.net>                                  |
// +----------------------------------------------------------------------+
//
// $Id: Display.php,v 1.2 2003/10/24 13:29:06 avb Exp $

require_once 'HTML/QuickForm/Action.php';

/**
 * This action handles the output of the form.
 * 
 * If you want to customize the form display, subclass this class and
 * override the _renderForm() method, you don't need to change the perform()
 * method itself.
 * 
 * @author  Alexey Borzov <avb@php.net>
 * @package HTML_QuickForm_Controller
 * @version $Revision: 1.2 $
 */
class HTML_QuickForm_Action_Display extends HTML_QuickForm_Action
{
    function perform(&$page, $actionName)
    {
        $pageName = $page->getAttribute('name');
        // If the original action was 'display' and we have values in container then we load them
        // BTW, if the page was invalid, we should later call validate() to get the errors
        list(, $oldName) = $page->controller->getActionName();
        if ('display' == $oldName) {
            $data =& $page->controller->container();
            if (!empty($data['values'][$pageName])) {
                $page->loadValues($data['values'][$pageName]);
                $validate = false === $data['valid'][$pageName];
            }
        }
        // set "common" defaults and constants
        $page->controller->applyDefaults($pageName);
        $page->isFormBuilt() or $page->buildForm();
        // if we had errors we should show them again
        if (isset($validate) && $validate) {
            $page->validate();
        }
        $this->_renderForm($page);
    }


   /**
    * Actually outputs the form.
    * 
    * If you want to customize the form's appearance (you most certainly will),
    * then you should override this method. There is no need to override perform()
    * 
    * @access public
    * @param  object HTML_QuickForm_Page  the page being processed
    */
    function _renderForm(&$page)
    {
        $page->display();
    }
}
?>
