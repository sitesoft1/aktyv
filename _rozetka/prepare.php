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
$error_log = __DIR__ . '/var/error_log.txt';
$sizes_log = __DIR__ . '/var/check_sizes.txt';

$off = __DIR__ . '/var/prepare_off.txt'; @unlink($off);
$on = __DIR__ . '/var/prepare_on.txt'; file_put_contents($on, 'ON!!!');
//files END
?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
$db = new Db;

$oc_category_result = $db->query("SELECT * FROM ".DB_PREFIX."category WHERE `roz_status`='1'");
//dump($oc_category_result);

while( $oc_category_row = mysqli_fetch_assoc($oc_category_result) ){
    //dump($oc_category_row);
    $category_id = $oc_category_row['category_id'];
    $roz_category_id = $oc_category_row['roz_category_id'];
    $category_roz_ratio = $oc_category_row['roz_ratio'];
    
    //dump($oc_product_to_category_result);
    //$oc_product_result = $db->query("SELECT * FROM `oc_product` WHERE `product_id` IN(SELECT `product_id` FROM `oc_product_to_category` WHERE `category_id`='$category_id' AND `main_category`='1') AND `status`='1' AND `quantity`>0");
    
    //$oc_product_result = $db->query("SELECT p.*, u.keyword FROM oc_product p LEFT OUTER JOIN oc_url_alias u ON u.query=CONCAT('product_id=', p.product_id) WHERE p.product_id IN(SELECT `product_id` FROM `oc_product_to_category` WHERE `category_id`='$category_id' AND `main_category`='1') AND p.status='1' AND p.quantity>0");
    $oc_product_result = $db->query("SELECT p.*, u.keyword, m.name as vendor, m.roz_description as vendor_roz_description, pd.name as product_name, pd.description as product_description
                                        FROM oc_product p
                                        LEFT OUTER JOIN oc_url_alias u ON u.query=CONCAT('product_id=', p.product_id)
                                        LEFT OUTER JOIN oc_manufacturer_description m ON m.manufacturer_id=p.manufacturer_id
                                        LEFT OUTER JOIN oc_product_description pd ON pd.product_id=p.product_id
                                        WHERE p.product_id IN(SELECT `product_id` FROM `oc_product_to_category` WHERE `category_id`='$category_id' AND `main_category`='1')
                                        AND p.status='1'
                                        AND p.quantity>0
                                        AND m.language_id='1'
                                        AND pd.language_id='1'");
    
    $cnt = 0;
    while( $oc_product_row = mysqli_fetch_assoc($oc_product_result) ){
        dump($oc_product_row);
        
        //Сформируем данные для вставки в таблицу
        $product_id = $oc_product_row['product_id'];
        $option_id = '';
        $option_value_id = '';
        $url = HTTPS_SERVER.$oc_product_row['keyword'];
        $price = $oc_product_row['price'];
        $name = $oc_product_row['product_name'];
        $description = $oc_product_row['product_description'];
        $pictures = '<picture>'.HTTPS_SERVER.$oc_product_row['image'].'</picture>';
        $oc_product_image_result = $db->query("SELECT `image` FROM `oc_product_image` WHERE product_id='$product_id'");
        if($oc_product_image_result){
            while( $oc_product_image_row = mysqli_fetch_assoc($oc_product_image_result) ){
                $pictures .= '<picture>'.HTTPS_SERVER.$oc_product_image_row['image'].'</picture>';
            }
        }
        $params = '';
        $vendor = $oc_product_row['vendor'];
        $vendor_roz_description = $oc_product_row['vendor_roz_description'];
        $color = '';
        $size = '';
        $stock_quantity = $oc_product_row['quantity'];
        $available = 'true';
        //Сформируем данные для вставки в таблицу КОНЕЦ
        
        
        $cnt++;
        
    }
    dump($cnt);
    
}

@unlink($on); file_put_contents($off, 'OFF!!!');
?>