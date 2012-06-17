<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997 - 2003 The PHP Group                              |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author:  Klaus Guenther <klaus@capitalfocus.org>                     |
// +----------------------------------------------------------------------+
//
// $Id: CSS.php,v 1.13 2003/11/03 21:12:39 thesaur Exp $

require_once "PEAR.php";
require_once "HTML/Common.php";

/**
 * Base class for CSS definitions
 *
 * This class handles the details for creating properly constructed CSS declarations.
 *
 * Example for direct output of stylesheet:
 * <code>
 * require_once 'HTML/CSS.php';
 * 
 * $css = new HTML_CSS();
 * 
 * // define styles
 * $css->setStyle('body', 'background-color', '#0c0c0c');
 * $css->setStyle('body', 'color', '#ffffff');
 * $css->setStyle('h1', 'text-align', 'center');
 * $css->setStyle('h1', 'font', '16pt helvetica, arial, sans-serif');
 * $css->setStyle('p', 'font', '12pt helvetica, arial, sans-serif');
 *
 * // output the stylesheet directly to browser
 * $css->display();
 * </code>
 *
 * Example of group usage:
 * <code>
 * require_once 'HTML/CSS.php';
 *
 * $css = new HTML_CSS();
 *
 * // create new group
 * $group1 = $css->createGroup('body, html');
 * $group2 = $css->createGroup('p, div');
 *
 * // define styles
 * $css->setGroupStyle($group1, 'background-color', '#0c0c0c');
 * $css->setGroupStyle($group1, 'color', '#ffffff');
 * $css->setGroupStyle($group2, 'text-align', 'left');
 * $css->setGroupStyle($group2, 'background-color', '#ffffff');
 * $css->setGroupStyle($group2, 'color', '#0c0c0c');
 * $css->setStyle('h1', 'text-align', 'center');
 * $css->setStyle('h1', 'font', '16pt helvetica, arial, sans-serif');
 * $css->setStyle('p', 'font', '12pt helvetica, arial, sans-serif');
 *
 * // output the stylesheet directly to browser
 * $css->display();
 * </code>
 *
 * Example in combination with HTML_Page:
 * <code>
 * require_once 'HTML/Page.php';
 * require_once 'HTML/CSS.php';
 * 
 * $css = new HTML_CSS();
 * $css->setStyle('body', 'background-color', '#0c0c0c');
 * $css->setStyle('body', 'color', '#ffffff');
 * $css->setStyle('h1', 'text-align', 'center');
 * $css->setStyle('h1', 'font', '16pt helvetica, arial, sans-serif');
 * $css->setStyle('p', 'font', '12pt helvetica, arial, sans-serif');
 *
 * $p = new HTML_Page();
 *
 * $p->setTitle("My page");
 * // it can be added as an object
 * $p->addStyleDeclaration($css, 'text/css');
 * $p->setMetaData("author", "My Name");
 * $p->addBodyContent("<h1>headline</h1>");
 * $p->addBodyContent("<p>some text</p>");
 * $p->addBodyContent("<p>some more text</p>");
 * $p->addBodyContent("<p>yet even more text</p>");
 * $p->display();
 * </code>
 * 
 * Example for generating inline code:
 * <code>
 * require_once 'HTML/CSS.php';
 * 
 * $css = new HTML_CSS();
 * 
 * $css->setStyle('body', 'background-color', '#0c0c0c');
 * $css->setStyle('body', 'color', '#ffffff');
 * $css->setStyle('h1', 'text-align', 'center');
 * $css->setStyle('h1', 'font', '16pt helvetica, arial, sans-serif');
 * $css->setStyle('p', 'font', '12pt helvetica, arial, sans-serif');
 * $css->setSameStyle('body', 'p');
 * 
 * echo '<body style="' . $css->toInline('body') . '">';
 * // will output:
 * // <body style="font:12pt helvetica, arial, sans-serif;background-color:#0c0c0c;color:#ffffff;">
 * </code>
 *
 * @author     Klaus Guenther <klaus@capitalfocus.org>
 * @package    HTML_CSS
 * @version    0.3.0
 * @access     public
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 */
class HTML_CSS extends HTML_Common {
    
    /**
     * Contains the CSS definitions.
     *
     * @var     array
     * @access  private
     */
    var $_css = array();
    
    /**
     * Contains "alibis" (other elements that share a definition) of an element defined in CSS
     *
     * @var     array
     * @access  private
     */
    var $_alibis = array();
    
    /**
     * Controls caching of the page
     *
     * @var     bool
     * @access  private
     */
    var $_cache = true;
    
    /**
     * Contains the character encoding string
     *
     * @var     string
     * @access  private
     */
    var $_charset = 'iso-8859-1';

    /**
     * Contains grouped styles
     *
     * @var     array
     * @access  private
     */
    var $_groups = array();
    
    /**
     * Number of CSS definition groups
     *
     * @var     int
     * @access  private
     */
    var $_groupCount = 0;

    /**
     * Class constructor
     *
     * @access  public
     */
    function HTML_CSS()
    {
        $commonVersion = 1.7;
        if (HTML_Common::apiVersion() < $commonVersion) {
            return PEAR::raiseError("HTML_CSS version " . $this->apiVersion() . " requires " .
            "HTML_Common version 1.2 or greater.", 0, PEAR_ERROR_TRIGGER);
        }
    }
    
    /**
     * Returns the current API version
     *
     * @access   public
     * @return   double
     */
    function apiVersion()
    {
        return 0.3;
    } // end func apiVersion
    
    /**
     * Creates a new CSS definition group. Returns an integer identifying the group.
     *
     * @param    string  $selectors   Selector(s) to be defined, comma delimited.
     * @param    mixed   $identifier  Group identifier. If not passed, will return an automatically assigned integer.
     * @return   int
     * @access   public
     */
    function createGroup($selectors, $identifier = null)
    {
        if ($identifier === null) {
            $this->_groupCount++;
            $group = $this->_groupCount;
        } else {
            if (isset($this->_groups[$identifier])){
                return PEAR::raiseError("HTML_CSS::createGroup() error: group $identifier already exists.",
                                            0, PEAR_ERROR_TRIGGER);
            }
            $group = $identifier;
        }
        $selectors =  explode(',', $selectors);
        foreach ($selectors as $selector) {
            $selector = trim($selector);
            $this->_groups[$group]['selectors'][] = $selector;
            $this->_alibis[$selector][$group] = true;
        }
        if ($identifier == null){
            return $group;
        }
    } // end func createGroup

    /**
     * Sets or adds a CSS definition for a CSS definition group
     *
     * @param    int     $group     CSS definition group identifier
     * @access   public
     */
    function unsetGroup($group)
    {
        $grp = $this->_checkGroup($group, 'unsetGroup');
        if (PEAR::isError($grp)) {
            return $grp;
        }
        
        foreach ($this->_groups[$group]['selectors'] as $selector) {
            unset ($this->_alibis[$selector][$group]);
            if (count($this->_alibis[$selector]) == 0) {
                unset($this->_alibis[$selector]);
            }
        }
        unset($this->_groups[$group]);
    } // end func unsetGroup

    /**
     * Sets or adds a CSS definition for a CSS definition group
     *
     * @param    int     $group     CSS definition group identifier
     * @param    string  $property  Property defined
     * @param    string  $value     Value assigned
     * @access   public
     */
    function setGroupStyle($group, $property, $value)
    {
        $grp = $this->_checkGroup($group, 'setGroupStyle');
        if (PEAR::isError($grp)) {
            return $grp;
        }
        $this->_groups[$group]['properties'][$property]= $value;
    } // end func setGroupStyle

    /**
     * Returns a CSS definition for a CSS definition group
     *
     * @param    int     $group     CSS definition group identifier
     * @param    string  $property  Property defined
     * @return   string
     * @access   public
     */
    function getGroupStyle($group, $property)
    {
        $grp = $this->_checkGroup($group, 'getGroupStyle');
        if (PEAR::isError($grp)) {
            return $grp;
        }
        return $this->_groups[$group]['properties'][$property];
    } // end func getGroupStyle

    /**
     * Adds a selector to a CSS definition group.
     *
     * @param    int     $group       CSS definition group identifier
     * @param    string  $selectors   Selector(s) to be defined, comma delimited.
     * @return   int
     * @access   public
     */
    function addGroupSelector($group, $selectors)
    {
        $grp = $this->_checkGroup($group, 'addGroupStyle');
        if (PEAR::isError($grp)) {
            return $grp;
        }
        $selectors =  explode(',', $selectors);
        foreach ($selectors as $selector) {
            $selector = trim($selector);
            $this->_groups[$group]['selectors'][] = $selector;
            $this->_alibis[$selector][$group] = true;
        }
    } // end func addGroupSelector

    /**
     * Removes a selector from a group.
     *
     * @param    int     $group       CSS definition group identifier
     * @param    string  $selectors   Selector(s) to be removed, comma delimited.
     * @return   int
     * @access   public
     */
    function removeGroupSelector($group, $selectors)
    {
        $grp = $this->_checkGroup($group, 'removeGroupSelector');
        if (PEAR::isError($grp)) {
            return $grp;
        }
        $selectors =  explode(',', $selectors);
        foreach ($selectors as $selector) {
            $selector = trim($selector);
            foreach ($this->_groups[$group]['selectors'] as $key => $value) {
                if ($value == $selector) {
                    unset($this->_groups[$group]['selectors'][$key]);
                }
            }
            unset($this->_alibis[$selector][$group]);
        }
    } // end func removeGroupSelector

    /**
     * Check if a group is valid (exists)
     *
     * @param    int     $group       CSS definition group identifier
     * @param    sring   $method      comes from
     * @return   bool                 TRUE if group exists, PEAR error otherwise
     * @access   private
     */
    function _checkGroup($group, $method)
    {
        if ($group < 0 || $group > $this->_groupCount) {
            return PEAR::raiseError("HTML_CSS::$method() error: group $group does not exist.",
                                        0, PEAR_ERROR_TRIGGER);
        }
        return true;
    }
    
    /**
     * Sets or adds a CSS definition
     *
     * @param    string  $element   Element (or class) to be defined
     * @param    string  $property  Property defined
     * @param    string  $value     Value assigned
     * @access   public
     */
    function setStyle ($element, $property, $value)
    {
        $this->_css[$element][$property]= $value;
    } // end func setStyle
    
    /**
     * Retrieves the value of a CSS property
     *
     * @param    string  $element   Element (or class) to be defined
     * @param    string  $property  Property defined
     * @access   public
     */
    function getStyle($element, $property)
    {
        $elm = $this->_checkElement($element, 'getStyle');
        if (PEAR::isError($elm)) {
            return $elm;
        }
        return $this->_css[$element][$property];
    } // end func getStyle
    
    /**
     * Sets or changes the properties of new selectors to the values of an existing selector
     *
     * @param    string  $old    Selector that is already defined
     * @param    string  $new    New selector(s) that should share the same definitions, separated by commas
     * @access   public
     */
    function setSameStyle ($new, $old)
    {
        $elm = $this->_checkElement($old, 'setSameStyle');
        if (PEAR::isError($elm)) {
            return $elm;
        }
        $others =  explode(',', $new);
        foreach ($others as $other) {
            $other = trim($other);
            foreach($this->_css[$old] as $property => $value) {
                $this->_css[$other][$property] = $value;
            }
        }
    } // end func setSameStyle
    
    /**
     * Check if an element is valid (exists)
     *
     * @param    string  $element     Element already defined
     * @param    sring   $method      comes from
     * @return   bool                 TRUE if group exists, PEAR error otherwise
     * @access   private
     */
    function _checkElement($element, $method)
    {
        if (!isset($this->_css[$element])) {
            return PEAR::raiseError("HTML_CSS::$method() error: element $element does not exist.",
                                        0, PEAR_ERROR_TRIGGER);
        }
        return true;
    }

    /**
     * Defines if the document should be cached by the browser. Defaults to false.
     *
     * @param string $cache Options are currently 'true' or 'false'. Defaults to 'true'.
     * @access public
     */
    function setCache($cache = 'true')
    {
        if ($cache == 'true'){
            $this->_cache = true;
        } else {
            $this->_cache = false;
        }
    } // end func setCache
    
    /**
     * Defines the charset for the file. defaults to ISO-8859-1 because of CSS1
     * compatability issue for older browsers.
     *
     * @param string $type Charset encoding; defaults to ISO-8859-1.
     * @access public
     */
    function setCharset($type = 'iso-8859-1')
    {
        $this->_charset = $type;
    } // end func setCharset
    
    /**
     * Returns the charset encoding string
     *
     * @access public
     */
    function getCharset()
    {
        return $this->_charset;
    } // end func getCharset
    
    /**
     * Parse a textstring that contains css information
     *
     * @param    string  $str    text string to parse
     * @since    0.3.0
     * @access   public
     * @return   void
     */
    function parseString($str) 
    {
        // Remove comments
        $str = preg_replace("/\/\*(.*)?\*\//Usi", "", $str);
        
        // Parse each element of csscode
        $parts = explode("}",$str);
        foreach($parts as $part) {
            $part = trim($part);
            if (strlen($part) > 0) {
                
                // Parse each group of element in csscode
                list($keystr,$codestr) = explode("{",$part);
                
                // Check if there are any groups.
                if (strpos($keystr, ',')) {
                    $group = $this->createGroup($keystr);
                    
                    // Parse each property of an element
                    $codes = explode(";",trim($codestr));
                    foreach ($codes as $code) {
                        if (strlen($code) > 0) {
                            list($property,$value) = explode(":",trim($code));
                            $this->setGroupStyle($group, $property, $value);
                        }
                    }
                } else {
                    
                    // let's get on with regular definitions
                    $key = trim($keystr);
                    if (strlen($key) > 0) {
                        // Parse each property of an element
                        $codes = explode(";",trim($codestr));
                        foreach ($codes as $code) {
                            if (strlen($code) > 0) {
                                list($property,$value) = explode(":",trim($code));
                                $this->setStyle($key, $property, $value);
                            }
                        }
                    }
                }
            }
        }
    } // end func parseString
    
    /**
     * Parse a file that contains CSS information
     *
     * @param    string  $filename    file to parse
     * @since    0.3.0
     * @return   void
     * @access   public
     */
    function parseFile($filename) 
    { 
        if (file_exists($filename)) {
            if (function_exists('file_get_contents')){
                $this->parseString(file_get_contents($filename));
            } else {
                $file = fopen("$filename", "rb");
                $this->parseString(fread($file, filesize($filename)));
                fclose($file);
            }
            
        } else {
            return PEAR::raiseError("HTML_CSS::parseFile() error: $filename does not exist.",
                                        0, PEAR_ERROR_TRIGGER);
        }
    } // func parseFile
    
    /**
     * Generates and returns the array of CSS properties
     *
     * @return  array
     * @access  public
     */
    function toArray()
    {
        // initialize $alibis
        $alibis = array();

        $newCssArray = array();

        // If there are groups, iterate through the array and generate the CSS
        if (count($this->_groups) > 0) {
            foreach ($this->_groups as $group) {

                // Start group definition
                foreach ($group['selectors'] as $selector){
                    $selector = trim($selector);
                    $alibis[] = $selector;
                }
                $alibis = implode(', ',$alibis);

                foreach ($group['properties'] as $key => $value) {
                    $newCssArray[$alibis][$key] = $value;
                }
                unset($alibis);

            }
        }

        // Iterate through the array and process each element
        foreach ($this->_css as $element => $property) {

            foreach ($property as $key => $value) {
                $newCssArray[$element][$key] = $value;
            }

        }
        return $newCssArray;
    } // end func toArray
    
    /**
     * Generates and returns the CSS properties of an element or class as a string for inline use.
     *
     * @param   string  $element    Element or class for which inline CSS should be generated
     * @return  string
     * @access  public
     */
    function toInline($element)
    {
        $strCss = '';
        $newCssArray = '';
        
        // Iterate through the array of properties for the supplied element
        // This allows for grouped elements definitions to work
        if (isset($this->_alibis[$element])) {
            foreach ($this->_alibis[$element] as $group => $status) {
                foreach ($this->_groups[$group]['properties'] as $key => $value) {
                    $newCssArray[$key] = $value;
                }
            }
        }
        
        // The reason this comes second is because if something is defined twice,
        // the value specifically assigned to this element should override
        // values inherited from other element definitions
        if ($this->_css[$element]) {
            foreach ($this->_css[$element] as $key => $value) {
                if ($key != 'other-elements') {
                    $newCssArray[$key] = $value;
                }
            }
        }
        
        foreach ($newCssArray as $key => $value) {
            $strCss .= $key . ':' . $value . ";";
        }
        
        // Let's roll!
        return $strCss;
    } // end func toInline
    
    /**
     * Generates CSS and stores it in a file.
     *
     * @return  void
     * @since   0.3.0
     * @access  public
     */
    function toFile($filename)
    {
        if (function_exists('file_put_content')){
            file_put_content($filename, $this->toString());
        } else {
            $file = fopen($filename,'wb');
            fwrite($file, $this->toString());
            fclose($file);
        }
        if (!file_exists($filename)){
            return PEAR::raiseError("HTML_CSS::toFile() error: Failed to write to $filename",
                                        0, PEAR_ERROR_TRIGGER);
        }
        
    } // end func toFile
    
    /**
     * Generates and returns the complete CSS as a string.
     *
     * @return string
     * @access public
     */
    function toString()
    {
        // get line endings
        $lnEnd = $this->_getLineEnd();
        $tabs = $this->_getTabs();
        $tab = $this->_getTab();
        
        // initialize $alibis
        $alibis = array();
        
        $strCss = '';
        
        // Allow a CSS comment
        if ($this->_comment) {
            $strCss = $tabs . '/* ' . $this->getComment() . ' */' . $lnEnd;
        }
        
        // If there are groups, iterate through the array and generate the CSS
        if (count($this->_groups) > 0) {
            foreach ($this->_groups as $group) {

                // Start group definition
                foreach ($group['selectors'] as $selector){
                    $selector = trim($selector);
                    $alibis[] = $selector;
                }
                $alibis = implode(', ',$alibis);
                $strCss .= $tabs . $alibis . ' {' . $lnEnd;
                unset($alibis);
                
                // Add CSS definitions
                foreach ($group['properties'] as $key => $value) {
                    $strCss .= $tabs . $tab . $key . ': ' . $value . ';' . $lnEnd;
                }
                $strCss .= $tabs . '}' . $lnEnd;
            }
        }
        
        // Iterate through the array and process each element
        foreach ($this->_css as $element => $property) {
            $strCss .= $lnEnd;

            //start CSS element definition
            $strCss .= $tabs . $element . ' {' . $lnEnd;
            
            foreach ($property as $key => $value) {
                $strCss .= $tabs . $tab . $key . ': ' . $value . ';' . $lnEnd;
            }
            
            // end CSS element definition
            $strCss .= $tabs . '}' . $lnEnd;
        }
        
        // Let's roll!
        return $strCss;
    } // end func toString
    
    /**
     * Outputs the stylesheet to the browser.
     *
     * @access    public
     */
    function display()
    {
        $lnEnd = $this->_getLineEnd();
        
        if(! $this->_cache) {
            header("Expires: Tue, 1 Jan 1980 12:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
        }
        
        // set character encoding
        header("Content-Type: text/css; charset=" . $this->_charset);
        
        $strCss = $this->toString();
        print $strCss;
    } // end func display
}
?>
