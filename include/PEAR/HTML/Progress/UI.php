<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Laurent Laville <pear@laurent-laville.org>                   |
// +----------------------------------------------------------------------+
//
// $Id: UI.php,v 1.6 2003/11/15 13:30:26 Farell Exp $

/**
 * The HTML_Progress_UI class provides a basic look and feel 
 * implementation of a progress bar.
 *
 * @version    1.0
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @access     public
 * @category   HTML
 * @package    HTML_Progress
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @todo       better aligment renders when auto-size progress is false
 */

require_once ('HTML/Common.php');

class HTML_Progress_UI extends HTML_Common
{
    /**
     * Whether the progress bar is horizontal or vertical.
     * The default is horizontal.
     *
     * @var        integer
     * @since      1.0
     * @access     private
     * @see        getOrientation(), setOrientation()
     */
    var $_orientation;

    /**
     * Whether the progress bar is filled in 'natural' or 'reverse' way.
     * The default fill way is 'natural'.
     *
     * <ul>
     * <li>since 0.5 : 'way'  =  bar fill way 
     *   <ul>
     *     <li>with Progress Bar Horizontal, 
     *              natural way is : left to right
     *        <br />reverse way is : right to left
     *     <li>with Progress Bar Vertical, 
     *              natural way is : down to up
     *        <br />reverse way is : up to down
     *   </ul>
     * </ul>
     *
     * @var        string
     * @since      1.0
     * @access     private
     * @see        getFillWay(), setFillWay()
     */
    var $_fillWay;

    /**
     * The cell count of the progress bar. The default is 10.
     *
     * @var        integer
     * @since      1.0
     * @access     private
     * @see        getCellCount(), setCellCount()
     */
    var $_cellCount;

    /**
     * The progress bar's structure 
     *
     * <ul>
     * <li>['cell']
     *     <ul>
     *     <li>since 1.0 : 'id'             =  cell identifier mask
     *     <li>since 1.0 : 'class'          =  css class selector
     *     <li>since 0.1 : 'width'          =  cell width
     *     <li>since 0.1 : 'height'         =  cell height
     *     <li>since 0.1 : 'active-color'   =  active color
     *     <li>since 0.1 : 'inactive-color' =  inactive color
     *     <li>since 0.1 : 'spacing'        =  cell spacing
     *     <li>since 0.6 : 'color'          =  foreground color
     *     <li>since 0.6 : 'font-size'      =  font size
     *     <li>since 0.6 : 'font-family'    =  font family
     *     </ul>
     * <li>['border']
     *     <ul>
     *     <li>since 1.0 : 'class'  =  css class selector
     *     <li>since 0.1 : 'width'  =  border width
     *     <li>since 0.1 : 'style'  =  border style
     *     <li>since 0.1 : 'color'  =  border color
     *     </ul>
     * <li>['string']
     *     <ul>
     *     <li>sicne 1.0 : 'id'                =  string identifier
     *     <li>since 0.6 : 'width'             =  with of progress string
     *     <li>since 0.6 : 'height'            =  height of progress string
     *     <li>since 0.1 : 'font-family'       =  font family
     *     <li>since 0.1 : 'font-size'         =  font size
     *     <li>since 0.1 : 'color'             =  font color
     *     <li>since 0.6 : 'background-color'  =  background color
     *     <li>since 0.6 : 'align'             =  horizontal align  (left, center, right, justify)
     *     <li>since 0.6 : 'valign'            =  vertical align  (top, bottom, left, right)
     *     </ul>
     * <li>['progress']
     *     <ul>
     *     <li>since 1.0 : 'class'             =  css class selector
     *     <li>since 0.1 : 'background-color'  =  bar background color
     *     <li>since 1.0 : 'auto-size'         = compute best progress size
     *     <li>since 0.1 : 'width'             =  bar width
     *     <li>since 0.1 : 'height'            =  bar height
     *     </ul>
     * </ul>
     *
     * @var        array
     * @since      1.0
     * @access     private
     * @see        HTML_Progress::toArray()
     */
    var $_progress = array();

    /**
     * External Javascript file to override internal default code
     *
     * @var        string
     * @since      1.0
     * @access     private
     * @see        getScript(), setScript()
     */
    var $_script;

    /**
     * Package name used by Error_Raise functions
     *
     * @var        string
     * @since      1.0
     * @access     private
     */
    var $_package;


    /**
     * The progress bar's UI model class constructor
     *
     * Constructor Summary
     *
     * o Creates a natural horizontal progress bar that displays ten cells/units.
     *   <code>
     *   $html = new HTML_Progress_UI();
     *   </code>
     *
     * o Creates a natural horizontal progress bar with the specified cell count, 
     *   which cannot be less than 1 (minimum), but has no maximum limit.
     *   <code>
     *   $html = new HTML_Progress_UI($cell);
     *   </code>
     *
     * @since      1.0
     * @access     public
     * @throws     HTML_PROGRESS_ERROR_INVALID_INPUT
     */
    function HTML_Progress_UI()
    {
        $this->_package = 'HTML_Progress_UI';
        Error_Raise::initialize($this->_package, array('HTML_Progress', '_getErrorMessage'));

        $args = func_get_args();

        switch (count($args)) {
         case 1:
            /*   int cell  */
            if (!is_int($args[0])) {
                return Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'exception',
                    array('var' => '$cell',
                          'was' => $args[0],
                          'expected' => 'integer',
                          'paramnum' => 1), PEAR_ERROR_TRIGGER);

            } elseif ($args[0] < 1) {
                return Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'error',
                    array('var' => '$cell',
                          'was' => $args[0],
                          'expected' => 'greater or equal 1',
                          'paramnum' => 1), PEAR_ERROR_TRIGGER);
            }
            $this->_cellCount = $args[0];
            break;
         default:
            $this->_cellCount = 10;
            break;
        }
        $this->_orientation = HTML_PROGRESS_BAR_HORIZONTAL;
        $this->_fillWay = 'natural';
        $this->_script = null;              // uses internal javascript code

        $this->_progress = array(
            'cell' => 
                array(
                    'id' => "progressCell%01s",
                    'class' => "cell",
                    'active-color' => "#006600",
                    'inactive-color' => "#CCCCCC",
                    'font-family' => "Courier, Verdana",
                    'font-size' => 8,
                    'color' => "#000000",
                    'width' => 15,
                    'height' => 20,
                    'spacing' => 2
                ),
            'border' => 
                array(
                    'class' => "progressBarBorder",
                    'width' => 0,
                    'style' => "solid",
                    'color' => "#000000"
                ),
            'string' => 
                array(
                    'id' => "installationProgress",
                    'width' => 50,
                    'font-family' => "Verdana, Arial, Helvetica, sans-serif",
                    'font-size' => 12,
                    'color' => "#000000",
                    'background-color' => "#FFFFFF",
                    'align' => "right",
                    'valign' => "right"
                ),
            'progress' => 
                array(
                    'class' => "progressBar",
                    'background-color' => "#FFFFFF",
                    'auto-size' => true
                )
        );
        $this->_updateProgressSize();   // updates the new size of progress bar
    }

    /**
     * Returns HTML_PROGRESS_BAR_HORIZONTAL or HTML_PROGRESS_BAR_VERTICAL,
     * depending on the orientation of the progress bar.
     * The default orientation is HTML_PROGRESS_BAR_HORIZONTAL.
     *
     * @return     integer
     * @since      1.0
     * @access     public
     * @see        setOrientation()
     */
    function getOrientation()
    {
        return $this->_orientation;
    }

    /**
     * Sets the progress bar's orientation, which must be HTML_PROGRESS_BAR_HORIZONTAL
     * or HTML_PROGRESS_BAR_VERTICAL.
     * The default orientation is HTML_PROGRESS_BAR_HORIZONTAL.
     *
     * @param      integer   $orient        Orientation (horizontal or vertical)
     *
     * @return     void
     * @since      1.0
     * @access     public
     * @throws     HTML_PROGRESS_ERROR_INVALID_INPUT
     * @see        getOrientation()
     */
    function setOrientation($orient)
    {
        if (!is_int($orient)) {
            return Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'exception',
                array('var' => '$orient',
                      'was' => gettype($orient),
                      'expected' => 'integer',
                      'paramnum' => 1), PEAR_ERROR_TRIGGER);

        } elseif (($orient != HTML_PROGRESS_BAR_HORIZONTAL) && 
                  ($orient != HTML_PROGRESS_BAR_VERTICAL)) {
            return Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'error',
                array('var' => '$orient',
                      'was' => $orient,
                      'expected' => HTML_PROGRESS_BAR_HORIZONTAL.' | '.
                                    HTML_PROGRESS_BAR_VERTICAL,
                      'paramnum' => 1), PEAR_ERROR_TRIGGER);
        }

        $previous = $this->_orientation;    // gets previous orientation
        $this->_orientation = $orient;      // sets the new orientation

        if ($previous != $orient) {
            // if orientation has changed, we need to swap cell width and height
            $w = $this->_progress['cell']['width'];
            $h = $this->_progress['cell']['height'];

            $this->_progress['cell']['width']  = $h;
            $this->_progress['cell']['height'] = $w;
                                            
            $this->_updateProgressSize();   // updates the new size of progress bar
        }
    }

    /**
     * Returns 'natural' or 'reverse', depending of the fill way of progress bar.
     * For horizontal progress bar, natural way is from left to right, and reverse 
     * way is from right to left.
     * For vertical progress bar, natural way is from down to up, and reverse 
     * way is from up to down.
     * The default fill way is 'natural'.
     *
     * @return     string
     * @since      1.0
     * @access     public
     * @see        setFillWay()
     */
    function getFillWay()
    {
        return $this->_fillWay;
    }

    /**
     * Sets the progress bar's fill way, which must be 'natural' or 'reverse'.
     * The default fill way is 'natural'.
     *
     * @param      string    $way           fill direction (natural or reverse)
     *
     * @return     void
     * @since      1.0
     * @access     public
     * @throws     HTML_PROGRESS_ERROR_INVALID_INPUT
     * @see        getFillWay()
     */
    function setFillWay($way)
    {
        if (!is_string($way)) {
            return Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'exception',
                array('var' => '$way',
                      'was' => gettype($way),
                      'expected' => 'string',
                      'paramnum' => 1), PEAR_ERROR_TRIGGER);

        } elseif ((strtolower($way) != 'natural') && (strtolower($way) != 'reverse')) {
            return Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'error',
                array('var' => '$way',
                      'was' => $way,
                      'expected' => 'natural | reverse',
                      'paramnum' => 1), PEAR_ERROR_TRIGGER);
        }
        $this->_fillWay = strtolower($way);
    }

    /**
     * Returns the number of cell in the progress bar. The default value is 10.
     *
     * @return     integer
     * @since      1.0
     * @access     public
     * @see        setCellCount()
     */
    function getCellCount()
    {
        return $this->_cellCount;
    }

    /**
     * Sets the number of cell in the progress bar
     *
     * @param      integer   $cells         Cell count on progress bar
     *
     * @return     void
     * @since      1.0
     * @access     public
     * @throws     HTML_PROGRESS_ERROR_INVALID_INPUT
     * @see        getCellCount()
     * @tutorial   beginner.pkg#look-and-feel.cell-style
     */
    function setCellCount($cells)
    {
        if (!is_int($cells)) {
            return Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'exception',
                array('var' => '$cells',
                      'was' => gettype($cells),
                      'expected' => 'integer',
                      'paramnum' => 1), PEAR_ERROR_TRIGGER);

        } elseif ($cells < 1) {
            return Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'error',
                array('var' => '$cells',
                      'was' => $cells,
                      'expected' => 'greater or equal 1',
                      'paramnum' => 1), PEAR_ERROR_TRIGGER);
        }
        $this->_cellCount = $cells;

        $this->_updateProgressSize();   // updates the new size of progress bar
    }

    /**
     * Returns the common and private cell attributes. Assoc array (defaut) or string
     *
     * @param      bool      $asString      (optional) whether to return the attributes as string 
     *
     * @return     mixed
     * @since      1.0
     * @access     public
     * @throws     HTML_PROGRESS_ERROR_INVALID_INPUT
     * @see        setCellAttributes()
     */
    function getCellAttributes($asString = false)
    {
        if (!is_bool($asString)) {
            return Error_Raise::exception($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT,
                array('var' => '$asString',
                      'was' => gettype($asString),
                      'expected' => 'boolean',
                      'paramnum' => 1));
        }

        $attr = $this->_progress['cell'];

        if ($asString) {
            return $this->_getAttrString($attr);
        } else {
            return $attr;
        }
    }

    /**
     * Sets the cell attributes for an existing cell.
     *
     * Defaults are:
     * <ul>
     * <li>Common :
     *     <ul>
     *     <li>id             = progressCell%01s
     *     <li>class          = cell
     *     <li>spacing        = 2
     *     <li>active-color   = #006600
     *     <li>inactive-color = #CCCCCC
     *     <li>font-family    = Courier, Verdana
     *     <li>font-size      = lowest value from cell width, cell height, and font size
     *     <li>color          = #000000
     *     <li>Horizontal Bar :
     *         <ul>
     *         <li>width      = 15
     *         <li>height     = 20
     *         </ul>
     *     <li>Vertical Bar :
     *         <ul>
     *         <li>width      = 20
     *         <li>height     = 15
     *         </ul>
     *     </ul>
     * </ul>
     *
     * @param      mixed     $attributes    Associative array or string of HTML tag attributes
     * @param      int       $cell          (optional) Cell index
     *
     * @return     void
     * @since      1.0
     * @access     public
     * @throws     HTML_PROGRESS_ERROR_INVALID_INPUT
     * @see        getCellAttributes()
     * @tutorial   beginner.pkg#look-and-feel.cell-style
     */
    function setCellAttributes($attributes, $cell = null)
    {
        if (!is_null($cell)) {
            if (!is_int($cell)) {
                return Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'exception',
                    array('var' => '$cell',
                          'was' => gettype($cell),
                          'expected' => 'integer',
                          'paramnum' => 1), PEAR_ERROR_TRIGGER);

            } elseif ($cell < 0) {
                return Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'error',
                    array('var' => '$cell',
                          'was' => $cell,
                          'expected' => 'positive',
                          'paramnum' => 1), PEAR_ERROR_TRIGGER);

            } elseif ($cell >= $this->getCellCount()) {
                return Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'error',
                    array('var' => '$cell',
                          'was' => $cell,
                          'expected' => 'less than '.$this->getCellCount(),
                          'paramnum' => 1), PEAR_ERROR_TRIGGER);
            }

            $this->_updateAttrArray($this->_progress['cell'][$cell], $this->_parseAttributes($attributes));
        } else {
            $this->_updateAttrArray($this->_progress['cell'], $this->_parseAttributes($attributes));
        }
        
        $font_size   = $this->_progress['cell']['font-size'];
        $cell_width  = $this->_progress['cell']['width'];
        $cell_height = $this->_progress['cell']['height'];
        $margin = ($this->getOrientation() == HTML_PROGRESS_BAR_HORIZONTAL) ? 0 : 3;

        $font_size = min(min($cell_width, $cell_height) - $margin, $font_size);
        $this->_progress['cell']['font-size'] = $font_size;

        $this->_updateProgressSize();   // updates the new size of progress bar
    }

    /**
     * Returns the progress bar's border attributes. Assoc array (defaut) or string.
     *
     * @param      bool      $asString      (optional) whether to return the attributes as string 
     *
     * @return     mixed
     * @since      1.0
     * @access     public
     * @throws     HTML_PROGRESS_ERROR_INVALID_INPUT
     * @see        setBorderAttributes()
     */
    function getBorderAttributes($asString = false)
    {
        if (!is_bool($asString)) {
            return Error_Raise::exception($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT,
                array('var' => '$asString',
                      'was' => gettype($asString),
                      'expected' => 'boolean',
                      'paramnum' => 1));
        }

        $attr = $this->_progress['border'];

        if ($asString) {
            return $this->_getAttrString($attr);
        } else {
            return $attr;
        }
    }

    /**
     * Sets the progress bar's border attributes.
     *
     * Defaults are:
     * <ul>
     * <li>class   = progressBarBorder
     * <li>width   = 0
     * <li>style   = solid
     * <li>color   = #000000
     * </ul>
     *
     * @param      mixed     $attributes    Associative array or string of HTML tag attributes
     *
     * @return     void
     * @since      1.0
     * @access     public
     * @see        getBorderAttributes()
     * @tutorial   beginner.pkg#look-and-feel.border-style
     * @example    bluesand.php             A thin solid border to a horizontal progress bar
     */
    function setBorderAttributes($attributes)
    {
        $this->_updateAttrArray($this->_progress['border'], $this->_parseAttributes($attributes));

        $this->_updateProgressSize();   // updates the new size of progress bar
    }

    /**
     * Returns the string attributes. Assoc array (defaut) or string.
     *
     * @param      bool      $asString      (optional) whether to return the attributes as string 
     *
     * @return     mixed
     * @since      1.0
     * @access     public
     * @throws     HTML_PROGRESS_ERROR_INVALID_INPUT
     * @see        setStringAttributes()
     */
    function getStringAttributes($asString = false)
    {
        if (!is_bool($asString)) {
            return Error_Raise::exception($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT,
                array('var' => '$asString',
                      'was' => gettype($asString),
                      'expected' => 'boolean',
                      'paramnum' => 1));
        }

        $attr = $this->_progress['string'];

        if ($asString) {
            return $this->_getAttrString($attr);
        } else {
            return $attr;
        }
    }

    /**
     * Sets the string attributes.
     *
     * Defaults are:
     * <ul>
     * <li>id                = installationProgress
     * <li>width             = 50
     * <li>font-family       = Verdana, Arial, Helvetica, sans-serif
     * <li>font-size         = 12
     * <li>color             = #000000
     * <li>background-color  = #FFFFFF
     * <li>align             = right
     * <li>Horizontal Bar :
     *     <ul>
     *     <li>valign        = right
     *     </ul>
     * <li>Vertical Bar :
     *     <ul>
     *     <li>valign        = bottom
     *     </ul>
     * </ul>
     *
     * @param      mixed     $attributes    Associative array or string of HTML tag attributes
     *
     * @return     void
     * @since      1.0
     * @access     public
     * @see        getStringAttributes()
     * @tutorial   beginner.pkg#look-and-feel.string-style
     */
    function setStringAttributes($attributes)
    {
        $this->_updateAttrArray($this->_progress['string'], $this->_parseAttributes($attributes));
    }

    /**
     * Returns the progress attributes. Assoc array (defaut) or string.
     *
     * @param      bool      $asString      (optional) whether to return the attributes as string 
     *
     * @return     mixed
     * @since      1.0
     * @access     public
     * @throws     HTML_PROGRESS_ERROR_INVALID_INPUT
     * @see        setProgressAttributes()
     */
    function getProgressAttributes($asString = false)
    {
        if (!is_bool($asString)) {
            return Error_Raise::exception($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT,
                array('var' => '$asString',
                      'was' => gettype($asString),
                      'expected' => 'boolean',
                      'paramnum' => 1));
        }

        $attr = $this->_progress['progress'];

        if ($asString) {
            return $this->_getAttrString($attr);
        } else {
            return $attr;
        }
    }

    /**
     * Sets the common progress bar attributes.
     *
     * Defaults are:
     * <ul>
     * <li>class             = progressBar
     * <li>background-color  = #FFFFFF
     * <li>auto-size         = true
     * <li>Horizontal Bar :
     *     <ul>
     *     <li>width         = (cell_count * (cell_width + cell_spacing)) + cell_spacing
     *     <li>height        = cell_height + (2 * cell_spacing)
     *     </ul>
     * <li>Vertical Bar :
     *     <ul>
     *     <li>width         = cell_width + (2 * cell_spacing)
     *     <li>height        = (cell_count * (cell_height + cell_spacing)) + cell_spacing
     *     </ul>
     * </ul>
     *
     * @param      mixed     $attributes    Associative array or string of HTML tag attributes
     *
     * @return     void
     * @since      1.0
     * @access     public
     * @see        getProgressAttributes()
     * @tutorial   beginner.pkg#look-and-feel.progress-style
     */
    function setProgressAttributes($attributes)
    {
        $this->_updateAttrArray($this->_progress['progress'], $this->_parseAttributes($attributes));
    }

    /**
     * Get the javascript code to manage progress bar.
     *
     * @return     string                   JavaScript URL or inline code to manage progress bar
     * @since      0.5
     * @access     public
     * @see        setScript()
     * @author     Stefan Neufeind <pear.neufeind@speedpartner.de> Contributor.
     *             See details on thanks section of README file.
     * @author     Christian Wenz <wenz@php.net> Helper.
     *             See details on thanks section of README file.
     */
    function getScript()
    {
        if (!is_null($this->_script)) {
            return $this->_script;   // URL to the linked Progress JavaScript
        }

        $js = <<< JS
var isDom = document.getElementById?true:false;
var isIE  = document.all?true:false;
var isNS4 = document.layers?true:false;
var cellCount = %cellCount%;

function setprogress(pIdent, pValue, pString, pDeterminate)
{
        if (isDom)
            prog = document.getElementById(pIdent+'%installationProgress%');
        if (isIE)
            prog = document.all[pIdent+'%installationProgress%'];
        if (isNS4)
            prog = document.layers[pIdent+'%installationProgress%'];
	if (prog != null) 
	    prog.innerHTML = pString;

        if (pValue == pDeterminate) {
	    for (i=0; i < cellCount; i++) {
                showCell(i, pIdent, "hidden");	
            }
        }
        if ((pDeterminate > 0) && (pValue > 0)) {
            i = (pValue-1) % cellCount;
            showCell(i, pIdent, "visible");	
        } else {
            for (i=pValue-1; i >=0; i--) {
                showCell(i, pIdent, "visible");	
            }
	}
}

function showCell(pCell, pIdent, pVisibility)
{
	if (isDom)
	    document.getElementById(pIdent+'%progressCell%'+pCell+'A').style.visibility = pVisibility;
	if (isIE)
	    document.all[pIdent+'%progressCell%'+pCell+'A'].style.visibility = pVisibility;
	if (isNS4)
	    document.layers[pIdent+'%progressCell%'+pCell+'A'].style.visibility = pVisibility;

}

JS;
        $cellAttr = $this->getCellAttributes();
        $attr = trim(sprintf($cellAttr['id'], '   '));
        $stringAttr = $this->getStringAttributes();
        $js = str_replace("%cellCount%", $this->getCellCount(), $js);
        $js = str_replace("%installationProgress%", $stringAttr['id'], $js);
        $js = str_replace("%progressCell%", $attr, $js);
         
        return $js;
    }

    /**
     * Set the external JavaScript code (file) to manage progress element.
     *
     * @param      string    $url           URL to the linked Progress JavaScript
     *
     * @return     void
     * @since      1.0
     * @access     public
     * @throws     HTML_PROGRESS_ERROR_INVALID_INPUT
     * @see        getScript()
     */
    function setScript($url)
    {
        if (!is_null($url)) {
            if (!is_string($url)) {
                Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'exception',
                    array('var' => '$url',
                          'was' => gettype($url),
                          'expected' => 'string',
                          'paramnum' => 1), PEAR_ERROR_TRIGGER);

            } elseif (!is_file($url) || $url == '.' || $url == '..') {
                Error_Raise::raise($this->_package, HTML_PROGRESS_ERROR_INVALID_INPUT, 'error',
                    array('var' => '$url',
                          'was' => $url.' file does not exists',
                          'expected' => 'javascript file exists',
                          'paramnum' => 1), PEAR_ERROR_TRIGGER);
            }
        }

        /*
         - since version 0.5.0,
         - default javascript code comes from getScript() method
         - but may be overrided by external file. 
        */
        $this->_script = $url;
    }

    /**
     * Get the cascading style sheet to put inline on HTML document
     *
     * @return     object                   HTML_CSS instance
     * @since      0.2
     * @access     public
     * @author     Stefan Neufeind <pear.neufeind@speedpartner.de> Contributor.
     *             See details on thanks section of README file.
     */
    function &getStyle()
    {
        include_once ('HTML/CSS.php');
        
        $progressAttr = $this->getProgressAttributes();
        $borderAttr = $this->getBorderAttributes();
        $stringAttr = $this->getStringAttributes();
        $cellAttr = $this->getCellAttributes();
        $orient = $this->getOrientation();
        
        $css = new HTML_CSS();

        $css->setStyle('.'.$progressAttr['class'], 'background-color', $progressAttr['background-color']);
        $css->setStyle('.'.$progressAttr['class'], 'width', $progressAttr['width'].'px');
        $css->setStyle('.'.$progressAttr['class'], 'height', $progressAttr['height'].'px');
        $css->setStyle('.'.$progressAttr['class'], 'position', 'relative');
        $css->setStyle('.'.$progressAttr['class'], 'left', '0px');
        $css->setStyle('.'.$progressAttr['class'], 'top', '0px');

        $css->setSameStyle('.'.$borderAttr['class'], '.'.$progressAttr['class']);
        $css->setStyle('.'.$borderAttr['class'], 'border-width', $borderAttr['width'].'px');
        $css->setStyle('.'.$borderAttr['class'], 'border-style', $borderAttr['style']);
        $css->setStyle('.'.$borderAttr['class'], 'border-color', $borderAttr['color']);

        $css->setStyle('.'.$stringAttr['id'], 'width', $stringAttr['width'].'px');
        if (isset($stringAttr['height'])) {
            $css->setStyle('.'.$stringAttr['id'], 'height', $stringAttr['height'].'px');
        }
        $css->setStyle('.'.$stringAttr['id'], 'text-align', $stringAttr['align']);
        $css->setStyle('.'.$stringAttr['id'], 'font-family', $stringAttr['font-family']);
        $css->setStyle('.'.$stringAttr['id'], 'font-size', $stringAttr['font-size'].'px');
        $css->setStyle('.'.$stringAttr['id'], 'color', $stringAttr['color']);
        $css->setStyle('.'.$stringAttr['id'], 'background-color', $stringAttr['background-color']);

        $css->setStyle('.'.$cellAttr['class'].'I', 'width', $cellAttr['width'].'px');
        $css->setStyle('.'.$cellAttr['class'].'I', 'height', $cellAttr['height'].'px');
        $css->setStyle('.'.$cellAttr['class'].'I', 'font-family', $cellAttr['font-family']);
        $css->setStyle('.'.$cellAttr['class'].'I', 'font-size', $cellAttr['font-size'].'px');

        if ($orient == HTML_PROGRESS_BAR_HORIZONTAL) {
            $css->setStyle('.'.$cellAttr['class'].'I', 'float', 'left'); 
        }
        if ($orient == HTML_PROGRESS_BAR_VERTICAL) {
            $css->setStyle('.'.$cellAttr['class'].'I', 'float', 'none'); 
        }
        $css->setSameStyle('.'.$cellAttr['class'].'A', '.'.$cellAttr['class'].'I');

        $css->setStyle('.'.$cellAttr['class'].'I', 'background-color', $cellAttr['inactive-color']);
        $css->setStyle('.'.$cellAttr['class'].'A', 'background-color', $cellAttr['active-color']);
        $css->setStyle('.'.$cellAttr['class'].'A', 'visibility', 'hidden');

        if (isset($cellAttr['background-image'])) {
            $css->setStyle('.'.$cellAttr['class'].'A', 'background-image', 'url("'.$cellAttr['background-image'].'")');
            $css->setStyle('.'.$cellAttr['class'].'A', 'background-repeat', 'no-repeat');
        }
        
        return $css;
    }

    /**
     * Updates the new size of progress bar, depending of cell size, cell count
     * and border width.
     *
     * @since      1.0
     * @access     private
     * @see        setOrientation(), setCellCount(), setCellAttributes(),
     *             setBorderAttributes()
     */
    function _updateProgressSize()
    {
        if (!$this->_progress['progress']['auto-size']) {
            return;
        }

        $cell_width   = $this->_progress['cell']['width'];
        $cell_height  = $this->_progress['cell']['height'];
        $cell_spacing = $this->_progress['cell']['spacing'];

        $border_width = $this->_progress['border']['width'];

        $cell_count = $this->_cellCount;

        if ($this->getOrientation() == HTML_PROGRESS_BAR_HORIZONTAL) {
            $w = ($cell_count * ($cell_width + $cell_spacing)) + $cell_spacing;
            $h = $cell_height + (2 * $cell_spacing);
        }
        if ($this->getOrientation() == HTML_PROGRESS_BAR_VERTICAL) {
            $w  = $cell_width + (2 * $cell_spacing);
            $h  = ($cell_count * ($cell_height + $cell_spacing)) + $cell_spacing;
        } 

        $attr = array ('width' => $w, 'height' => $h);

        $this->_updateAttrArray($this->_progress['progress'], $attr);
    }
}

?>