--TEST--
DB_driver::error mapping
--SKIPIF--
<?php chdir(dirname(__FILE__)); require_once './skipif.inc'; ?>
--FILE--
<?php
require_once './mktable.inc';
require_once '../errors.inc';
?>
--EXPECT--
Trying to provoke DB_ERROR_NOSUCHTABLE
  DB Error: no such table
Trying to provoke DB_ERROR_ALREADY_EXISTS
  DB Error: already exists
Trying to provoke DB_ERROR_NOSUCHTABLE
  DB Error: no such table
Trying to provoke DB_ERROR_CONSTRAINT
  DB Error: constraint violation
Trying to provoke DB_ERROR_DIVZERO
  DB Error: division by zero
Trying to provoke DB_ERROR_INVALID_NUMBER
  DB Error: invalid number
Trying to provoke DB_ERROR_NOSUCHFIELD
  DB Error: no such field
Trying to provoke DB_ERROR_SYNTAX
  DB Error: syntax error
