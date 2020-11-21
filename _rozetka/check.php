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
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/classes/db.php';
//Inkludim parser END

//files
$error_log = __DIR__ . '/var/check_error_log.txt';
$sizes_log = __DIR__ . '/var/check_sizes.txt';

$off = __DIR__ . '/var/check_off.txt'; @unlink($off);
$on = __DIR__ . '/var/check_on.txt'; file_put_contents($on, 'ON!!!');

$language_id = 1;//Язык 1- ру, 3 - уа.
//files END
?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
$db = new Db;

$oc_roz_unload_products_result = $db->query("SELECT * FROM `oc_roz_unload_products`");

while( $oc_roz_unload_products_row = mysqli_fetch_assoc($oc_roz_unload_products_result) ){
    $product_id = $oc_roz_unload_products_row['product_id'];
    $size_option_id = $oc_roz_unload_products_row['size_option_id'];
    $size_option_value_id = $oc_roz_unload_products_row['size_option_value_id'];
    
    if( empty($size_option_id) and empty($size_option_value_id) ){
        $oc_product_result = $db->query("SELECT * FROM `oc_product` WHERE `product_id`='$product_id' AND `status`='1' AND `quantity`>0");
        if(!$oc_product_result){
            $db->query_update("UPDATE `oc_roz_unload_products` SET available='false', `stock_quantity`='0' WHERE `product_id`='$product_id'");
        }
    }
    else{
        $oc_ocfilter_option_value_to_product_result = $db->query("SELECT * FROM `oc_ocfilter_option_value_to_product` WHERE `product_id`='$product_id' AND `option_id`='$size_option_id' AND `value_id`='$size_option_value_id'");
        if(!$oc_ocfilter_option_value_to_product_result){
            $db->query_update("UPDATE `oc_roz_unload_products` SET available='false', `stock_quantity`='0' WHERE `product_id`='$product_id' AND `size_option_id`='$size_option_id' AND `size_option_value_id`='$size_option_value_id'");
        }
    }
}

@unlink($on); file_put_contents($off, 'OFF!!!');
?>