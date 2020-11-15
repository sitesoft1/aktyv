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

$language_id = 1;//Язык 1- ру, 3 - уа.
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
    $roz_footwear = $oc_category_row['roz_footwear'];
    
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
                                        AND m.language_id='$language_id'
                                        AND pd.language_id='$language_id'");
    
    $cnt = 0;
    while( $oc_product_row = mysqli_fetch_assoc($oc_product_result) ){
        //dump($oc_product_row);
        
        //Сформируем данные для вставки в таблицу
        $product_id = $oc_product_row['product_id'];
        $manufacturer_id = $oc_product_row['manufacturer_id'];
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
        $vendor = $oc_product_row['vendor'];
        $vendor_roz_description = $oc_product_row['vendor_roz_description'];
        $stock_quantity = $oc_product_row['quantity'];
        $available = 'true';
    
        //Цвет
        $color = $db->query_assoc("SELECT `name` FROM `oc_option_value_description` WHERE option_id='14' AND language_id='1' AND option_value_id IN(SELECT option_value_id FROM `oc_product_option_value` WHERE product_id='$product_id' AND option_id='14') LIMIT 1","name");
        $color = my_mb_ucfirst($color);
        //Цвет КОНЕЦ
        
        //Параметры
        $params_options_result = $db->query("SELECT ovd.value_id AS option_value_id, ovd.option_id AS option_id, ovd.name AS option_value, ood.name AS option_name
                                            FROM oc_ocfilter_option_value_description ovd
                                            LEFT OUTER JOIN oc_ocfilter_option_description ood ON ood.option_id=ovd.option_id
                                            WHERE ovd.option_id IN(SELECT option_id FROM `oc_ocfilter_option_value_to_product` WHERE product_id='$product_id')
                                            AND ovd.value_id IN(SELECT value_id FROM `oc_ocfilter_option_value_to_product` WHERE product_id='$product_id')
                                            AND ovd.language_id='$language_id'
                                            AND ood.language_id='$language_id'
                                            AND ood.name NOT LIKE '%размер%'");
    
        $params = '';
        if($params_options_result){
            while( $params_options_row = mysqli_fetch_assoc($params_options_result) ){
                //show_strong("У товара $product_id параметры есть!");
                //dump($params_options_row);
    
                $params .= '<param name="'.$params_options_row['option_name'].'">'.$params_options_row['option_value'].'</param>';
            }
        }
        else{
            //show("У товара $product_id параметров нету!");
        }
        //Параметры КОНЕЦ
        
    
        //Получим опции фильтра
        //Размер
        $sizes_options_result = $db->query("SELECT ovd.value_id AS option_value_id, ovd.option_id AS option_id, ovd.name AS option_value, ood.name AS option_name
                                            FROM oc_ocfilter_option_value_description ovd
                                            LEFT OUTER JOIN oc_ocfilter_option_description ood ON ood.option_id=ovd.option_id
                                            WHERE ovd.option_id IN(SELECT option_id FROM `oc_ocfilter_option_value_to_product` WHERE product_id='$product_id')
                                            AND ovd.value_id IN(SELECT value_id FROM `oc_ocfilter_option_value_to_product` WHERE product_id='$product_id')
                                            AND ovd.language_id='$language_id'
                                            AND ood.language_id='$language_id'
                                            AND ood.name LIKE '%размер%'");
        
        if($sizes_options_result){
            while( $sizes_options_row = mysqli_fetch_assoc($sizes_options_result) ){
                //show_strong("У товара $product_id размеры есть!");
                //dump($sizes_options_row);
    
                $size_option_value = $sizes_options_row['option_value'];
                $size_option_name = my_mb_ucfirst($sizes_options_row['option_name']);
                $size_option_id = $sizes_options_row['option_id'];
                $size_option_value_id = $sizes_options_row['option_value_id'];
                
                
                //Здесь вставляем записи в базу
                $check = $db->query("SELECT * FROM `oc_roz_unload_products` WHERE `product_id`='$product_id' AND `size_option_id`='$size_option_id' AND `size_option_value_id`='$size_option_value_id'");
                if(!$check){
                    $insert_id = $db->query_insert_id("INSERT INTO `oc_roz_unload_products` (
                                    `product_id`,
                                    `size_option_id`,
                                    `size_option_value_id`,
                                    `url`,
                                    `price`,
                                    `category_id`,
                                    `roz_category_id`,
                                    `name`,
                                    `description`,
                                    `pictures`,
                                    `params`,
                                    `vendor`,
                                    `manufacturer_id`,
                                    `size_name`,
                                    `size_value`,
                                    `footwear`,
                                    `color`,
                                    `stock_quantity`,
                                    `available`)
                                    VALUES(
                                        '$product_id',
                                        '$size_option_id',
                                        '$size_option_value_id',
                                        '$url',
                                        '$price',
                                        '$category_id',
                                        '$roz_category_id',
                                        '$name',
                                        '$description',
                                        '$pictures',
                                        '$params',
                                        '$vendor',
                                        '$manufacturer_id',
                                        '$size_option_name',
                                        '$size_option_value',
                                        '$roz_footwear',
                                        '$color',
                                        '$stock_quantity',
                                        '$available')");
                    dump($insert_id);
                }
                else{
                    $db->query_update("UPDATE `oc_roz_unload_products` SET
                                        `url`='$url',
                                        `price`='$price',
                                        `category_id`='$category_id',
                                        `roz_category_id`='$roz_category_id',
                                        `name`='$name',
                                        `description`='$description',
                                        `pictures`='$pictures',
                                        `params`='$params',
                                        `vendor`='$vendor',
                                        `manufacturer_id`='$manufacturer_id',
                                        `size_name`='$size_option_name',
                                        `size_value`='$size_option_value',
                                        `footwear`='$roz_footwear',
                                        `color`='$color',
                                        `stock_quantity`='$stock_quantity',
                                        `available`='$available'
                                        WHERE `product_id`='$product_id' AND `size_option_id`='$size_option_id' AND `size_option_value_id`='$size_option_value_id'");
                    show("Товар $product_id уже существует и был обновлен");
                }
                //Здесь вставляем записи в базу КОНЕЦ
                
            }
        }
        else{
            $check = $db->query("SELECT * FROM `oc_roz_unload_products` WHERE `product_id`='$product_id'");
            if(!$check){
                $insert_id = $db->query_insert_id("INSERT INTO `oc_roz_unload_products` (
                                    `product_id`,
                                    `size_option_id`,
                                    `size_option_value_id`,
                                    `url`,
                                    `price`,
                                    `category_id`,
                                    `roz_category_id`,
                                    `name`,
                                    `description`,
                                    `pictures`,
                                    `params`,
                                    `vendor`,
                                    `manufacturer_id`,
                                    `size_name`,
                                    `size_value`,
                                    `color`,
                                    `stock_quantity`,
                                    `available`)
                                    VALUES('$product_id',
                                        '',
                                        '',
                                        '$url',
                                        '$price',
                                        '$category_id',
                                        '$roz_category_id',
                                        '$name',
                                        '$description',
                                        '$pictures',
                                        '$params',
                                        '$vendor',
                                        '$manufacturer_id',
                                        '',
                                        '',
                                        '$color',
                                        '$stock_quantity',
                                        '$available')");
                dump($insert_id);
            }
            else{
                $db->query_update("UPDATE `oc_roz_unload_products` SET
                                        `url`='$url',
                                        `price`='$price',
                                        `category_id`='$category_id',
                                        `roz_category_id`='$roz_category_id',
                                        `name`='$name',
                                        `description`='$description',
                                        `pictures`='$pictures',
                                        `params`='$params',
                                        `vendor`='$vendor',
                                        `manufacturer_id`='$manufacturer_id',
                                        `color`='$color',
                                        `stock_quantity`='$stock_quantity',
                                        `available`='$available'
                                        WHERE `product_id`='$product_id'");
                show("Товар $product_id уже существует и был обновлен");
            }
            
        }
        //Получим опции фильтра КОНЕЦ
        //Сформируем данные для вставки в таблицу КОНЕЦ
        $cnt++;
    }
    dump($cnt);
    
}

@unlink($on); file_put_contents($off, 'OFF!!!');
?>