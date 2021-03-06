<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit                                                        |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: TestResult.php,v 1.8 2004/01/04 10:25:10 sebastian Exp $
//

require_once 'PHPUnit/Framework/AssertionFailedError.php';
require_once 'PHPUnit/Framework/Test.php';
require_once 'PHPUnit/Framework/TestFailure.php';
require_once 'PHPUnit/Framework/TestListener.php';

/**
 * A TestResult collects the results of executing a test case.
 *
 * @package phpunit.framework
 * @author  Sebastian Bergmann <sb@sebastian-bergmann.de>
 */
class PHPUnit_Framework_TestResult {
    // {{{ Members

    /**
    * @var    array
    * @access protected
    */
    protected $errors = array();

    /**
    * @var    array
    * @access protected
    */
    protected $failures = array();

    /**
    * @var    array
    * @access protected
    */
    protected $listeners = array();

    /**
    * @var    integer
    * @access protected
    */
    protected $runTests = 0;

    /**
    * @var    boolean
    * @access private
    */
    private $stop = false;

    /**
    * Code Coverage information provided by Xdebug.
    *
    * @var    array
    * @access private
    */
    private $codeCoverageInformation = array();

    // }}}
    // {{{ public function addError(PHPUnit_Framework_Test $test, Exception $e)

    /**
    * Adds an error to the list of errors.
    * The passed in exception caused the error.
    *
    * @param  PHPUnit_Framework_Test  $test
    * @param  Exception               $e
    * @access public
    */
    public function addError(PHPUnit_Framework_Test $test, Exception $e) {
        $this->errors[] = new PHPUnit_Framework_TestFailure($test, $e);

        foreach ($this->listeners as $listener) {
            $listener->addError($test, $e);
        }
    }

    // }}}
    // {{{ public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e)

    /**
    * Adds a failure to the list of failures.
    * The passed in exception caused the failure.
    *
    * @param  PHPUnit_Framework_Test                  $test
    * @param  PHPUnit_Framework_AssertionFailedError  $e
    * @access public
    */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e) {
        $this->failures[] = new PHPUnit_Framework_TestFailure($test, $e);

        foreach ($this->listeners as $listener) {
            $listener->addFailure($test, $e);
        }
    }

    // }}}
    // {{{ public function addListener(PHPUnit_Framework_TestListener $listener)

    /**
    * Registers a TestListener.
    *
    * @param  PHPUnit_Framework_TestListener
    * @access public
    */
    public function addListener(PHPUnit_Framework_TestListener $listener) {
        $this->listeners[] = $listener;
    }

    // }}}
    // {{{ public function endTest(PHPUnit_Framework_Test $test)

    /**
    * Informs the result that a test was completed.
    *
    * @param  PHPUnit_Framework_Test
    * @access public
    */
    public function endTest(PHPUnit_Framework_Test $test) {
        $this->codeCoverageInformation[$test->getName()] = $test->getCodeCoverageInformation();

        foreach ($this->listeners as $listener) {
            $listener->endTest($test);
        }
    }

    // }}}
    // {{{ public function errorCount()

    /**
    * Gets the number of detected errors.
    *
    * @return integer
    * @access public
    */
    public function errorCount() {
        return sizeof($this->errors);
    }

    // }}}
    // {{{ public function errors()

    /**
    * Returns an Enumeration for the errors.
    *
    * @return array
    * @access public
    */
    public function errors() {
        return $this->errors;
    }

    // }}}
    // {{{ public function failureCount()

    /**
    * Gets the number of detected failures.
    *
    * @return integer
    * @access public
    */
    public function failureCount() {
        return sizeof($this->failures);
    }

    // }}}
    // {{{ public function failures()

    /**
    * Returns an Enumeration for the failures.
    *
    * @return array
    * @access public
    */
    public function failures() {
        return $this->failures;
    }

    // }}}
    // {{{ public function getCodeCoverageInformation()

    /**
    * Returns the Code Coverage information provided by Xdebug.
    *
    * @return array
    * @access public
    */
    public function getCodeCoverageInformation() {
        return $this->codeCoverageInformation;
    }

    // }}}
    // {{{ public function removeListener(PHPUnit_Framework_TestListener $listener)

    /**
    * Unregisters a TestListener.
    *
    * @param  PHPUnit_Framework_TestListener $listener
    * @access public
    */
    public function removeListener(PHPUnit_Framework_TestListener $listener) {
        for ($i = 0; $i < sizeof($this->listeners); $i++) {
            if ($this->listeners[$i] === $listener) {
                unset($this->listeners[$i]);
            }
        }
    }

    // }}}
    // {{{ public function run(PHPUnit_Framework_Test $test)

    /**
    * Runs a TestCase.
    *
    * @param  PHPUnit_Framework_Test $test
    * @access public
    */
    public function run(PHPUnit_Framework_Test $test) {
        $this->startTest($test);

        try {
            $test->runBare();
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            $this->addFailure($test, $e);
        }

        catch (Exception $e) {
            $this->addError($test, $e);
        }

        $this->endTest($test);
    }

    // }}}
    // {{{ public function runCount()

    /**
    * Gets the number of run tests.
    *
    * @return integer
    * @access public
    */
    public function runCount() {
        return $this->runTests;
    }

    // }}}
    // {{{ public function shouldStop()

    /**
    * Checks whether the test run should stop.
    *
    * @access public
    */
    public function shouldStop() {
        return $this->stop;
    }

    // }}}
    // {{{ public function startTest(PHPUnit_Framework_Test $test)

    /**
    * Informs the result that a test will be started.
    *
    * @param  PHPUnit_Framework_Test $test
    * @access public
    */
    public function startTest(PHPUnit_Framework_Test $test) {
        $this->runTests += $test->countTestCases();

        foreach ($this->listeners as $listener) {
            $listener->startTest($test);
        }
    }

    // }}}
    // {{{ public function stop()

    /**
    * Marks that the test run should stop.
    *
    * @access public
    */
    public function stop() {
        $this->stop = true;
    }

    // }}}
    // {{{ public function wasSuccessful()

    /**
    * Returns whether the entire test was successful or not.
    *
    * @return boolean
    * @access public
    */
    public function wasSuccessful() {
        if (empty($this->errors) && empty($this->failures)) {
            return true;
        } else {
            return false;
        }
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
