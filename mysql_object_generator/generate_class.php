<?php

include __DIR__ . '/../config/config.php';
include __DIR__ . '/../libs/Mysql.php';
$template = file_get_contents('class_template.txt');
$table = $argv[1];

$template = change('classname', ucwords($table), $template);
/*
 * public $id;
  public $name;
  public $status;
  public $parent_id;
 */
$query = Mysql::getInstance()->query("SELECT `COLUMN_NAME`,`DATA_TYPE` 
FROM `INFORMATION_SCHEMA`.`COLUMNS` 
WHERE `TABLE_SCHEMA`='dergipark' 
    AND `TABLE_NAME`='" . $table . "';");
$query->execute();
$columns = $query->fetchAll(PDO::FETCH_CLASS);
$var = '';
$c1 = $c2 = $c3 = array();
$vars = '';
$template = change('table_name', $table, $template);
foreach ($columns as $col) {
    $vars.="public $" . $col->COLUMN_NAME . ";\n";
    $c1[] = '$' . $col->COLUMN_NAME . "=NULL";
    $c2[] = '$this->' . $col->COLUMN_NAME . ' = $' . $col->COLUMN_NAME;
    $c3[] = '$this->' . $col->COLUMN_NAME . ' = $row[\'' . $col->COLUMN_NAME . '\'];';
}
$template = change('vars', $vars, $template);

$c1 = implode(',', $c1);
$c2 = implode(";\n", $c2);
$c3 = implode("\n", $c3);
// generate construct function
$contsruct = 'function __construct(' . $c1 . '){' . "\n" . $c2 . ";\n" . '}';
$template = change('construct_function', $contsruct, $template);
$template = change('loadobjectvars', $c3, $template);

write(__DIR__ . '/' . $table . '.php', $template);

function write($filePath, $string) {
    $f = fopen($filePath, 'w+');
    fwrite($f, $string);
    fclose($f);
}

function change($c, $v, $t) {
    return str_replace('{{' . $c . '}}', $v, $t);
}
