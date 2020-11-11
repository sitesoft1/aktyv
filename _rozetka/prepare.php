<?php
/*
nohup php -q prepare.php 2>&1 &
ili:
screen
php prepare.php

Ochistka pamyati
https://www.php.net/manual/ru/mysqli-result.free.php
*/
set_time_limit(0);
//vkluchaem vivod osibok php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//vkluchaem vivod osibok php
header('Content-type: text/html; charset=utf-8');
//require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/classes/db.php';
//Inkludim parser END

//files
$error_log = __DIR__ . '/var/error_log.txt';
$sizes_log = __DIR__ . '/var/check_sizes.txt';

$off = __DIR__ . '/var/off.txt'; @unlink($off);
$on = __DIR__ . '/var/on.txt'; file_put_contents($on, 'ON!!!');
//files END
?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
$db = new Db;

@unlink($on); file_put_contents($off, 'OFF!!!');

?>