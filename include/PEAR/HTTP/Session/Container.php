<?php
//
// +-----------------------------------------------------------------------+
// | Copyright (c) 2002, Alexander Radivanovich                            |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.|
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Alexander Radivanovich <info@wwwlab.net>                      |
// +-----------------------------------------------------------------------+
//

/**
 * Container class for storing session data data
 *
 * @author  Alexander Radivaniovich <info@wwwlab.net>
 * @package HTTP_Session
 * @access  public
 */
class HTTP_Session_Container
{

    /**
     * Additional options for the container object
     *
     * @var array
     * @access private
     */
    var $options = array();

    /**
     * Constrtuctor method
     *
     * @access public
     * @param  array  $options Additional options for the container object
     * @return void
     */
    function HTTP_Session_Container($options = null)
    {
        $this->_setDefaults();
        if (is_array($options)) {
            $this->_parseOptions();
        }
    }

    /**
     * Set some default options
     *
     * @access private
     */
    function _setDefaults()
    {
    }

    /**
     * Parse options passed to the container class
     *
     * @access private
     * @param array Options
     */
    function _parseOptions($options)
    {
        foreach ($options as $option => $value) {
            if (in_array($option, array_keys($this->options))) {
                $this->options[$option] = $value;
            }
        }
    }

    /**
     * This function is called by the session
     * handler to initialize things
     *
     * @access public
     */
    function open($save_path, $session_name)
    {
        return true;
    }

    /**
     * This function is called when the page is finished
     * executing and the session handler needs to close things off
     *
     * Has to be overwritten by each container class
     *
     * @access public
     */
    function close()
    {
        return true;
    }

    /**
     * This function is called by the session handler
     * to read the data associated with a given session ID.
     * This function must retrieve and return the session data
     * for the session identified by $id.
     *
     * Has to be overwritten by each container class
     *
     * @access public
     * @param  mixed  $id ID of the session
     * @return mixed      The data associated with a given session ID
     */
    function read($id)
    {
        return '';
    }

    /**
     * This function is called when the session handler
     * has session data to save, which usually happens
     * at the end of your script
     *
     * Has to be overwritten by each container class
     *
     * @access public
     * @param  mixed   $id   ID of the session
     * @param  mixed   $data The data associated with a given session ID
     * @return boolean Obvious
     */
    function write($id, $data)
    {
        return true;
    }

    /**
     * This function is called when a session is destroyed.
     * It is responsible for deleting the session and cleaning things up.
     *
     * Has to be overwritten by each container class
     *
     * @access public
     * @param  mixed  $id ID of the session
     * @return boolean Obvious
     */
    function destroy($id)
    {
        return true;
    }

    /**
     * This function is responsible for garbage collection.
     * In the case of session handling, it is responsible
     * for deleting old, stale sessions that are hanging around.
     * The session handler will call this every now and then.
     *
     * Has to be overwritten by each container class
     *
     * @access public
     * @param  integer $maxlifetime ???
     * @return boolean Obvious
     */
    function gc($maxlifetime)
    {
        return true;
    }

    /**
     * Set session save handler
     *
     * @access public
     * @return void
     */
    function set()
    {
        $GLOBALS['HTTP_Session_Container'] =& $this;
        session_module_name('user');
        session_set_save_handler(
            'HTTP_Session_Open',
            'HTTP_Session_Close',
            'HTTP_Session_Read',
            'HTTP_Session_Write',
            'HTTP_Session_Destroy',
            'HTTP_Session_GC'
        );
    }

}

// Delegate function calls to the object's methods
/** @ignore */
function HTTP_Session_Open($save_path, $session_name) { return $GLOBALS['HTTP_Session_Container']->open($save_path, $session_name); }
/** @ignore */
function HTTP_Session_Close()                         { return $GLOBALS['HTTP_Session_Container']->close(); }
/** @ignore */
function HTTP_Session_Read($id)                       { return $GLOBALS['HTTP_Session_Container']->read($id); }
/** @ignore */
function HTTP_Session_Write($id, $data)               { return $GLOBALS['HTTP_Session_Container']->write($id, $data); }
/** @ignore */
function HTTP_Session_Destroy($id)                    { return $GLOBALS['HTTP_Session_Container']->destroy($id); }
/** @ignore */
function HTTP_Session_GC($maxlifetime)                { return $GLOBALS['HTTP_Session_Container']->gc($maxlifetime); }

?>