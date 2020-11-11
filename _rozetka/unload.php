<?php
/*
nohup php -q prepare.php 2>&1 &
ili:
screen
php unload.php

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
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/classes/db.php';
//Inkludim parser END

//files
$error_log = __DIR__ . '/var/unload_error_log.txt';
$sizes_log = __DIR__ . '/var/unload_check_sizes.txt';
$off = __DIR__ . '/var/unload_off.txt'; @unlink($off);
$on = __DIR__ . '/var/unload_on.txt'; file_put_contents($on, 'ON!!!');
//files END

$temp_file = 'temp';
$final_file = 'auto';
$xml_id = '';

file_put_contents( whatFileTo($temp_file.$xml_id.'.xml'), '');
write('', whatFileTo('list.txt'), 0);

?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
$db = new Db;

$xml = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
$xml .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . PHP_EOL;
$xml .= '<yml_catalog date="' . date('Y-m-d H:i') . '">' . PHP_EOL;
$xml .= '<shop>' . PHP_EOL;
$xml .= '<name>aktyv</name>' . PHP_EOL;
$xml .= '<company>Магазин aktyv.com.ua</company>' . PHP_EOL;
$xml .= '<url>https://aktyv.com.ua/</url>' . PHP_EOL;
$xml .= '<phone>067-342-11-13</phone>' . PHP_EOL;
$xml .= '<platform>ocStore</platform>' . PHP_EOL;
$xml .= '<version>2.3.0.2.3</version>' . PHP_EOL;
$xml .= '<currencies><currency id="UAH" rate="1"/></currencies>' . PHP_EOL;
//Sohranim xml shapku na buduschee
$xml_header = $xml;

write($xml, whatFileTo($final_file.$xml_id.'.xml'), 1);

//$oc_category_result = $db->query("SELECT * FROM ".DB_PREFIX."category WHERE `roz_status`='1'");
//dump($oc_category_result);
//while( $oc_category_row = mysqli_fetch_assoc($oc_category_result) ){}

@unlink($on); file_put_contents($off, 'OFF!!!');
?>