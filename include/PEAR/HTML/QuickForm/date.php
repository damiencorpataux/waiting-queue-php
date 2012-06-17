<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
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
// | Authors: Alexey Borzov <avb@php.net>                                 |
// |          Adam Daniel <adaniel1@eesus.jnj.com>                        |
// |          Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id: date.php,v 1.40 2004/01/03 10:32:38 mansion Exp $

require_once 'HTML/QuickForm/group.php';
require_once 'HTML/QuickForm/select.php';

/**
 * Class for a group of elements used to input dates (and times).
 * 
 * Inspired by original 'date' element but reimplemented as a subclass
 * of HTML_QuickForm_group
 * 
 * @author Alexey Borzov <avb@php.net>
 * @access public
 */
class HTML_QuickForm_date extends HTML_QuickForm_group
{
    // {{{ properties

   /**
    * Various options to control the element's display.
    * 
    * Currently known options are
    * 'language': date language
    * 'format': Format of the date, based on PHP's date() function.
    *     The following characters are recognised in format string:
    *       D => Short names of days
    *       l => Long names of days
    *       d => Day numbers
    *       M => Short names of months
    *       F => Long names of months
    *       m => Month numbers
    *       Y => Four digit year
    *       y => Two digit year
    *       h => 12 hour format
    *       H => 23 hour  format
    *       i => Minutes
    *       s => Seconds
    *       a => am/pm
    *       A => AM/PM
    * 'minYear': Minimum year in year select
    * 'maxYear': Maximum year in year select
    * 'addEmptyOption': Should an empty option be added to the top of
    *     each select box?
    * 'emptyOptionValue': The value passed by the empty option.
    * 'emptyOptionText': The text displayed for the empty option.
    * 'optionIncrement': Step to increase the option values by (works for 'i' and 's')
    * 
    * @access   private
    * @var      array
    */
    var $_options = array(
        'language'         => 'en',
        'format'           => 'dMY',
        'minYear'          => 2001,
        'maxYear'          => 2010,
        'addEmptyOption'   => false,
        'emptyOptionValue' => '',
        'emptyOptionText'  => '&nbsp;',
        'optionIncrement'  => array('i' => 1, 's' => 1)
    );

   /**
    * These complement separators, they are appended to the resultant HTML
    * @access   private
    * @var      array
    */
    var $_wrap = array('', '');

   /**
    * Options in different languages
    * @access   private
    * @var      array
    */
    var $_locale = array(
        'en'    => array (
            'weekdays_short'=> array ('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'),
            'weekdays_long' => array ('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
            'months_long'   => array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')
        ),
        'de'    => array (
            'weekdays_short'=> array ('So', 'Mon', 'Di', 'Mi', 'Do', 'Fr', 'Sa'),
            'weekdays_long' => array ('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'),
            'months_short'  => array ('Jan', 'Feb', 'M�rz', 'April', 'Mai', 'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov', 'Dez'),
            'months_long'   => array ('Januar', 'Februar', 'M�rz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember')
        ),
        'fr'    => array (
            'weekdays_short'=> array ('Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'),
            'weekdays_long' => array ('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'),
            'months_short'  => array ('Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'),
            'months_long'   => array ('Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre')
        ),
        'hu'    => array (
            'weekdays_short'=> array ('V', 'H', 'K', 'Sze', 'Cs', 'P', 'Szo'),
            'weekdays_long' => array ('vas�rnap', 'h�tf�', 'kedd', 'szerda', 'cs�t�rt�k', 'p�ntek', 'szombat'),
            'months_short'  => array ('jan', 'feb', 'm�rc', '�pr', 'm�j', 'j�n', 'j�l', 'aug', 'szept', 'okt', 'nov', 'dec'),
            'months_long'   => array ('janu�r', 'febru�r', 'm�rcius', '�prilis', 'm�jus', 'j�nius', 'j�lius', 'augusztus', 'szeptember', 'okt�ber', 'november', 'december')
        ),
        'pl'    => array (
            'weekdays_short'=> array ('Nie', 'Pn', 'Wt', '�r', 'Czw', 'Pt', 'Sob'),
            'weekdays_long' => array ('Niedziela', 'Poniedzia�ek', 'Wtorek', '�roda', 'Czwartek', 'Pi�tek', 'Sobota'),
            'months_short'  => array ('Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Pa�', 'Lis', 'Gru'),
            'months_long'   => array ('Stycze�', 'Luty', 'Marzec', 'Kwiecie�', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpie�', 'Wrzesie�', 'Pa�dziernik', 'Listopad', 'Grudzie�')
        ),
        'sl'    => array (
            'weekdays_short'=> array ('Ned', 'Pon', 'Tor', 'Sre', 'Cet', 'Pet', 'Sob'),
            'weekdays_long' => array ('Nedelja', 'Ponedeljek', 'Torek', 'Sreda', 'Cetrtek', 'Petek', 'Sobota'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Avg', 'Sep', 'Okt', 'Nov', 'Dec'),
            'months_long'   => array ('Januar', 'Februar', 'Marec', 'April', 'Maj', 'Junij', 'Julij', 'Avgust', 'September', 'Oktober', 'November', 'December')
        ),
        'ru'    => array (
            'weekdays_short'=> array ('��', '��', '��', '��', '��', '��', '��'),
            'weekdays_long' => array ('�����������', '�����������', '�������', '�����', '�������', '�������', '�������'),
            'months_short'  => array ('���', '���', '���', '���', '���', '���', '���', '���', '���', '���', '���', '���'),
            'months_long'   => array ('������', '�������', '����', '������', '���', '����', '����', '������', '��������', '�������', '������', '�������')
        ),
        'es'    => array (
            'weekdays_short'=> array ('Dom', 'Lun', 'Mar', 'Mi�', 'Jue', 'Vie', 'S�b'),
            'weekdays_long' => array ('Domingo', 'Lunes', 'Martes', 'Mi�rcoles', 'Jueves', 'Viernes', 'S�bado'),
            'months_short'  => array ('Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'),
            'months_long'   => array ('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septimbre', 'Octubre', 'Noviembre', 'Diciembre')
        ),
        'da'    => array (
            'weekdays_short'=> array ('S�n', 'Man', 'Tir', 'Ons', 'Tor', 'Fre', 'L�r'),
            'weekdays_long' => array ('S�ndag', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'L�rdag'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'),
            'months_long'   => array ('Januar', 'Februar', 'Marts', 'April', 'Maj', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'December')
        ),
        'is'    => array (
            'weekdays_short'=> array ('Sun', 'M�n', '�ri', 'Mi�', 'Fim', 'F�s', 'Lau'),
            'weekdays_long' => array ('Sunnudagur', 'M�nudagur', '�ri�judagur', 'Mi�vikudagur', 'Fimmtudagur', 'F�studagur', 'Laugardagur'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Ma�', 'J�n', 'J�l', '�g�', 'Sep', 'Okt', 'N�v', 'Des'),
            'months_long'   => array ('Jan�ar', 'Febr�ar', 'Mars', 'Apr�l', 'Ma�', 'J�n�', 'J�l�', '�g�st', 'September', 'Okt�ber', 'N�vember', 'Desember')
        ),
        'it'    => array (
            'weekdays_short'=> array ('Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'),
            'weekdays_long' => array ('Domenica', 'Luned�', 'Marted�', 'Mercoled�', 'Gioved�', 'Venerd�', 'Sabato'),
            'months_short'  => array ('Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'),
            'months_long'   => array ('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre')
        ),
        'sk'    => array (
            'weekdays_short'=> array ('Ned', 'Pon', 'Uto', 'Str', '�tv', 'Pia', 'Sob'),
            'weekdays_long' => array ('Nede�a', 'Pondelok', 'Utorok', 'Streda', '�tvrtok', 'Piatok', 'Sobota'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'M�j', 'J�n', 'J�l', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'),
            'months_long'   => array ('Janu�r', 'Febru�r', 'Marec', 'Apr�l', 'M�j', 'J�n', 'J�l', 'August', 'September', 'Okt�ber', 'November', 'December')
        ),
        'cs'    => array (
            'weekdays_short'=> array ('Ne', 'Po', '�t', 'St', '�t', 'P�', 'So'),
            'weekdays_long' => array ('Ned�le', 'Pond�l�', '�ter�', 'St�eda', '�tvrtek', 'P�tek', 'Sobota'),
            'months_short'  => array ('Led', '�no', 'B�e', 'Dub', 'Kv�', '�en', '�ec', 'Srp', 'Z��', '��j', 'Lis', 'Pro'),
            'months_long'   => array ('Leden', '�nor', 'B�ezen', 'Duben', 'Kv�ten', '�erven', '�ervenec', 'Srpen', 'Z���', '��jen', 'Listopad', 'Prosinec')
        ),
        'hy'    => array(
            'weekdays_short'=> array ('���','���','���','���','�ݷ','���','޵�'),
            'weekdays_long' => array ('�����','�����߳���','���߳���','����߳���', '��ݷ߳���', '����', '޳���'),
            'months_short'  => array ('���','���','���','���','���','���','���','���','���','���','���','���'),
            'months_long'   => array ('������','������','س��','�����','س���','������','������','�������','���ٵ��','����ٵ��','��ۻٵ��','����ٵ��')
        ),
        'nl'    => array (
            'weekdays_short'=> array ('Zo', 'Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za'),
            'weekdays_long' => array ('Zondag', 'Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'),
            'months_long'   => array ('Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December')
        )
    );

    // }}}
    // {{{ constructor

   /**
    * Class constructor
    * 
    * @access   public
    * @param    string  Element's name
    * @param    mixed   Label(s) for an element
    * @param    array   Options to control the element's display
    * @param    mixed   Either a typical HTML attribute string or an associative array
    */
    function HTML_QuickForm_date($elementName = null, $elementLabel = null, $options = array(), $attributes = null)
    {
        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'date';
        // set the options, do not bother setting bogus ones
        if (is_array($options)) {
            foreach ($options as $name => $value) {
                if ('language' == $name) {
                    $this->_options['language'] = isset($this->_locale[$value])? $value: 'en';
                } elseif (isset($this->_options[$name])) {
                    if (is_array($value)) {
                        $this->_options[$name] = @array_merge($this->_options[$name], $value);
                    } else {
                        $this->_options[$name] = $value;
                    }
                }
            }
        }
    }

    // }}}
    // {{{ _createElements()

    function _createElements()
    {
        $this->_separator = $this->_elements = array();
        $separator =  '';
        $locale    =& $this->_locale[$this->_options['language']];
        $backslash =  false;
        for ($i = 0, $length = strlen($this->_options['format']); $i < $length; $i++) {
            $sign = $this->_options['format']{$i};
            if ($backslash) {
                $backslash  = false;
                $separator .= $sign;
            } else {
                $loadSelect = true;
                switch ($sign) {
                    case 'D':
                        // Sunday is 0 like with 'w' in date()
                        $options = $locale['weekdays_short'];
                        break;
                    case 'l':
                        $options = $locale['weekdays_long'];
                        break;
                    case 'd':
                        $options = $this->_createOptionList(1, 31);
                        break;
                    case 'M':
                        $options = $locale['months_short'];
                        array_unshift($options , '');
                        unset($options[0]);
                        break;
                    case 'm':
                        $options = $this->_createOptionList(1, 12);
                        break;
                    case 'F':
                        $options = $locale['months_long'];
                        array_unshift($options , '');
                        unset($options[0]);
                        break;
                    case 'Y':
                        $options = $this->_createOptionList(
                            $this->_options['minYear'],
                            $this->_options['maxYear'], 
                            $this->_options['minYear'] > $this->_options['maxYear']? -1: 1
                        );
                        break;
                    case 'y':
                        $options = $this->_createOptionList(
                            $this->_options['minYear'],
                            $this->_options['maxYear'],
                            $this->_options['minYear'] > $this->_options['maxYear']? -1: 1
                        );
                        array_walk($options, create_function('&$v,$k','$v = substr($v,-2);')); 
                        break;
                    case 'h':
                        $options = $this->_createOptionList(1, 12);
                        break;
                    case 'H':
                        $options = $this->_createOptionList(0, 23);
                        break;
                    case 'i':
                        $options = $this->_createOptionList(0, 59, $this->_options['optionIncrement']['i']);
                        break;
                    case 's':
                        $options = $this->_createOptionList(0, 59, $this->_options['optionIncrement']['s']);
                        break;
                    case 'a':
                        $options = array('am' => 'am', 'pm' => 'pm');
                        break;
                    case 'A':
                        $options = array('AM' => 'AM', 'PM' => 'PM');
                        break;
                    case '\\':
                        $backslash  = true;
                        $loadSelect = false;
                        break;
                    default:
                        $separator .= (' ' == $sign? '&nbsp;': $sign);
                        $loadSelect = false;
                }
    
                if ($loadSelect) {
                    if (0 < count($this->_elements)) {
                        $this->_separator[] = $separator;
                    } else {
                        $this->_wrap[0] = $separator;
                    }
                    $separator = '';
                    // Should we add an empty option to the top of the select?
                    if ($this->_options['addEmptyOption']) {
                        // Preserve the keys
                        $options = array($this->_options['emptyOptionValue'] => $this->_options['emptyOptionText']) + $options;
                    }
                    $this->_elements[] =& new HTML_QuickForm_select($sign, null, $options, $this->getAttributes());
                }
            }
        }
        $this->_wrap[1] = $separator . ($backslash? '\\': '');
    }

    // }}}
    // {{{ _createOptionList()

   /**
    * Creates an option list containing the numbers from the start number to the end, inclusive
    *
    * @param    int     The start number
    * @param    int     The end number
    * @param    int     Increment by this value
    * @access   private
    * @return   array   An array of numeric options.
    */
    function _createOptionList($start, $end, $step = 1)
    {
        for ($i = $start, $options = array(); $start > $end? $i >= $end: $i <= $end; $i += $step) {
            $options[$i] = sprintf('%02d', $i);
        }
        return $options;
    }

    // }}}
    // {{{ setValue()

    function setValue($value)
    {
        if ($this->_options['addEmptyOption'] && empty($value)) {
            $value = array();
        } else  if (!is_array($value)) {
            // might be a unix epoch, then we fill all possible values
            $arr = explode('-', date('w-d-n-Y-h-H-i-s-a-A', (int)$value));
            $value = array(
                'D' => $arr[0],
                'l' => $arr[0],
                'd' => $arr[1],
                'M' => $arr[2],
                'm' => $arr[2],
                'F' => $arr[2],
                'Y' => $arr[3],
                'y' => $arr[3],
                'h' => $arr[4],
                'H' => $arr[5],
                'i' => $arr[6],
                's' => $arr[7],
                'a' => $arr[8],
                'A' => $arr[9]
            );
        }
        parent::setValue($value);
    }

    // }}}
    // {{{ toHtml()

    function toHtml()
    {
        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer =& new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate($this->_wrap[0] . '{element}' . $this->_wrap[1]);
        parent::accept($renderer);
        return $renderer->toHtml();
    }

    // }}}
    // {{{ accept()

    function accept(&$renderer, $required = false, $error = null)
    {
        $renderer->renderElement($this, $required, $error);
    }

    // }}}
    // {{{ onQuickFormEvent()

    function onQuickFormEvent($event, $arg, &$caller)
    {
        if ('updateValue' == $event) {
            // we need to call setValue(), 'cause the default/constant value
            // may be in fact a timestamp, not an array
            return HTML_QuickForm_element::onQuickFormEvent($event, $arg, $caller);
        } else {
            return parent::onQuickFormEvent($event, $arg, $caller);
        }
    }

    // }}}
}
?>