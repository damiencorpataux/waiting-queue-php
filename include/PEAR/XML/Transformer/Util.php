<?php
//
// +---------------------------------------------------------------------------+
// | PEAR :: XML :: Transformer                                                |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2002-2003 Sebastian Bergmann <sb@sebastian-bergmann.de> and |
// |                         Kristian K�hntopp <kris@koehntopp.de>.            |
// +---------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,           |
// | that is available at http://www.php.net/license/3_0.txt.                  |
// | If you did not receive a copy of the PHP license and are unable to        |
// | obtain it through the world-wide-web, please send a note to               |
// | license@php.net so we can mail you a copy immediately.                    |
// +---------------------------------------------------------------------------+
//
// $Id: Util.php,v 1.9 2003/09/08 17:38:31 sebastian Exp $
//

/**
* Utility Methods.
*
* @author  Sebastian Bergmann <sb@sebastian-bergmann.de>
* @author  Kristian K�hntopp <kris@koehntopp.de>
* @version $Revision: 1.9 $
* @access  public
*/
class XML_Transformer_Util {
    // {{{ function attributesToString($attributes)

    /**
    * Returns string representation of attributes array.
    *
    * @param  array
    * @return string
    * @access public
    * @static
    */
    function attributesToString($attributes) {
        $string = '';

        if (is_array($attributes)) {
            ksort($attributes);

            foreach ($attributes as $key => $value) {
                $string .= ' ' . $key . '="' . $value . '"';
            }
        }

        return $string;
    }

    // }}}
    // {{{ function logMessage($logMessage, $target = 'error_log')

    /**
    * Sends an error message to a given target.
    *
    * @param  string
    * @param  string
    * @access public
    * @static
    */
    function logMessage($logMessage, $target = 'error_log') {
        switch ($target) {
            case 'echo':
            case 'print': {
                print $logMessage;
            }
            break;

            default: {
                error_log($logMessage);
            }
        }
    }

    // }}}
    // {{{ function qualifiedElement($element)

    /**
    * Returns namespace and qualified element name
    * for a given element.
    *
    * @param  string
    * @return array
    * @access public
    * @static
    */
    function qualifiedElement($element) {
        if (strstr($element, ':')) {
            return explode(':', $element);
        } else {
            return array('&MAIN', $element);
        }
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
