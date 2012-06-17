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
// $Id: Namespace.php,v 1.20 2003/01/18 18:01:23 sebastian Exp $
//

require_once 'XML/Transformer/Util.php';

/**
* Convenience Base Class for Namespace Transformers.
*
* Example
*
*   <?php
*   require_once 'XML/Transformer.php';
*   require_once 'XML/Transformer/Namespace.php';
*
*   class Image extends XML_Transformer_Namespace {
*       var $imageAttributes = array();
*
*       function truePath($path) {
*           if (php_sapi_name() == 'apache') {
*               $r    = apache_lookup_uri($path);
*               $path = $r->filename;
*           } else {
*               $path = $_SERVER['DOCUMENT_ROOT'] . "/$path";
*           }
*
*           return $path;
*       }
*
*       function start_img($attributes) {
*           $this->imageAttributes = $attributes;
*           return '';
*       }
*
*       function end_img($cdata) {
*           $src = $this->truePath($this->imageAttributes['src']);
*           list($w, $h, $t, $whs) = getimagesize($src);
*
*           $this->imageAttributes['height'] = $w;
*           $this->imageAttributes['width']  = $h;
*
*           return sprintf(
*             '<img %s/>',
*             XML_Transformer::attributesToString($this->imageAttributes)
*           );
*       }
*   }
*   ?>
*
* @author  Sebastian Bergmann <sb@sebastian-bergmann.de>
* @author  Kristian K�hntopp <kris@koehntopp.de>
* @version $Revision: 1.20 $
* @access  public
*/
class XML_Transformer_Namespace {
    // {{{ Members

    /**
    * @var    string
    * @access public
    */
    var $defaultNamespacePrefix = '';

    /**
    * @var    boolean
    * @access public
    */
    var $secondPassRequired = false;

    /**
    * @var    array
    * @access private
    */
    var $_prefix = array();

    /**
    * @var    string
    * @access private
    */
    var $_transformer = '';

    // }}}
    // {{{ function initObserver($prefix, &$object)

    /**
    * Called by XML_Transformer at initialization time.
    * We use this to remember our namespace prefixes
    * (there can be multiple) and a pointer to the
    * Transformer object.
    *
    * @param  string
    * @param  object
    * @access public
    */
    function initObserver($prefix, &$object) {
      $this->_prefix[]    = $prefix;
      $this->_transformer = $object;
    }

    // }}}
    // {{{ function startElement($element, $attributes)

    /**
    * Wrapper for startElement handler.
    *
    * @param  string
    * @param  array
    * @return string
    * @access public
    */
    function startElement($element, $attributes) {
        $do = 'start_' . $element;

        if (method_exists($this, $do)) {
            return $this->$do($attributes);
        }

        return sprintf(
          "<%s%s>",

          $element,
          XML_Transformer_Util::attributesToString($attributes)
        );
    }

    // }}}
    // {{{ function endElement($element, $cdata)

    /**
    * Wrapper for endElement handler.
    *
    * @param  string
    * @param  string
    * @return array
    * @access public
    */
    function endElement($element, $cdata) {
        $do = 'end_' . $element;

        if (method_exists($this, $do)) {
            return $this->$do($cdata);
        }

        return array(
          sprintf(
            '%s</%s>',

            $cdata,
            $element
          ),
          false
        );
    }

    // }}}
    // {{{ function function getLock()

    /**
    * Lock all other namespace handlers.
    *
    * @return boolean
    * @access public
    * @see    releaseLock()
    */
    function getLock() {
        return $this->_transformer->_callbackRegistry->getLock($this->_prefix[0]);
    }

    // }}}
    // {{{ function releaseLock()

    /**
    * Releases a lock.
    *
    * @access public
    * @see    getLock()
    */
    function releaseLock() {
        $this->_transformer->_callbackRegistry->releaseLock();
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
