--TEST--
DB_driver::quote
--SKIPIF--
<?php chdir(dirname(__FILE__)); require_once './skipif.inc'; ?>
--FILE--
<?php
require_once './connect.inc';


/**
 * Local error callback handler.
 *
 * Drops the phptest table, prints out an error message and kills the
 * process.
 *
 * @param object  $o  PEAR error object automatically passed to this method
 * @return void
 * @see PEAR::setErrorHandling()
 */
function pe($o) {
    global $dbh;

    $dbh->setErrorHandling(PEAR_ERROR_RETURN);
    $dbh->query('DROP TABLE pearquote');

    die($o->toString());
}

// DBMS boolean column type simulation...
$boolean_col_type = array(
    'dbase'  => 'Logical',
    'fbase'  => 'BOOLEAN',
    'ibase'  => 'SMALLINT',
    'ifx'    => 'SMALLINT',
    'msql'   => 'INTEGER',
    'mssql'  => 'BIT',
    'mysql'  => 'TINYINT(1)',
    'mysql4' => 'TINYINT(1)',
    'oci8'   => 'NUMBER(1)',
    'odbc'   => 'SMALLINT',
    'pgsql'  => 'BOOLEAN',
    'sqlite' => 'INTEGER',
    'sybase' => 'TINYINT',
);

// adjust things for specific DBMS's
switch ($dbh->phptype) {
    case 'odbc':
    case 'ibase':
        $null = '';
        $chr  = 'VARCHAR(8)';
        break;
    case 'msql':
    case 'ifx':
        // doing this for ifx to keep certain versions happy
        $null = '';
        $chrc = 'CHAR(255)';
        $chr  = 'CHAR(8)';
        break;
    default:
        $null = 'NULL';
        $chr  = 'VARCHAR(8)';
}

$dbh->setErrorHandling(PEAR_ERROR_RETURN);
$dbh->query('DROP TABLE pearquote');

$dbh->setErrorHandling(PEAR_ERROR_CALLBACK, 'pe');

$dbh->query("
    CREATE TABLE pearquote (
      n DECIMAL(3,1) $null,
      s $chr $null,
      b {$boolean_col_type[$dbh->phptype]} $null
    )
");

$strings = array(
    "'",
    "\"",
    "\\",
    "%",
    "_",
    "''",
    "\"\"",
    "\\\\",
    "\\'\\'",
    "\\\"\\\""
);

$nums = array(
    12.3,
    15,
);

$bools = array(
    TRUE,
    FALSE,
);


echo "String escape test: ";
foreach ($strings as $s) {
    $quoted = $dbh->quoteSmart($s);
    $dbh->query("INSERT INTO pearquote (s) VALUES ($quoted)");
}
$diff = array_diff($strings, $res = $dbh->getCol("SELECT s FROM pearquote"));
if (count($diff) > 0) {
    echo "FAIL";
    print_r($strings);
    print_r($res);
} else {
    echo "OK";
}

$dbh->query("DELETE FROM pearquote");


echo "\nNumber escape test: ";
foreach ($nums as $n) {
    $quoted = $dbh->quoteSmart($n);
    $dbh->query("INSERT INTO pearquote (n) VALUES ($quoted)");
}

$diff = array();
$res =& $dbh->getCol('SELECT n FROM pearquote ORDER BY n');
foreach ($nums as $key => $val) {
    if ($val != $res[$key]) {
        $diff[] = "$val != {$res[$key]}";
    }
}

if (count($diff) > 0) {
    echo "FAIL";
    print_r($nums);
    print_r($res);
} else {
    echo 'OK';
}

$dbh->query('DELETE FROM pearquote');


echo "\nBoolean escape test: ";
$i = 1;
foreach ($bools as $b) {
    $quoted = $dbh->quoteSmart($b);
    $dbh->query("INSERT INTO pearquote (n, b) VALUES ($i, $quoted)");
    $i++;
}

$diff = array();
$res =& $dbh->getCol('SELECT b FROM pearquote ORDER BY n');
foreach ($bools as $key => $val) {
    if ($val === true) {
        if ($res[$key] != 1 || $res[$key] != true) {
            $diff[] = "in:true != out:{$res[$key]}";
        }
    } else {
        if ($res[$key] != 0 || $res[$key] != false) {
            $diff[] = "in:false != out:{$res[$key]}";
        }
    }
}

if (count($diff) > 0) {
    echo 'FAIL';
    print_r($nums);
    print_r($res);
} else {
    echo "OK\n";
}


$dbh->setErrorHandling(PEAR_ERROR_RETURN);
$dbh->query('DROP TABLE pearquote');

?>
--EXPECT--
String escape test: OK
Number escape test: OK
Boolean escape test: OK
