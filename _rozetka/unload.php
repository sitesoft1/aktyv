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

$rozetka_cats_rezult = $db->query("SELECT * FROM `oc_roz_categories`");

$xml .= '<categories>' . PHP_EOL;
while( $rozetka_cats_row = mysqli_fetch_assoc($rozetka_cats_rezult) ){
    time_nanosleep(0, 10000000);
    
    $category_id = $rozetka_cats_row['category_id'];
    $name = $rozetka_cats_row['name'];
    $parent_id = $rozetka_cats_row['parent_id'];
    
    if($parent_id>0){
        $xml .= '<category id="' . $category_id . '" parentId="' . $parent_id . '">' . $name . '</category>' . PHP_EOL;
    }else{
        $xml .= '<category id="' . $category_id . '">' . $name . '</category>' . PHP_EOL;
    }
    
}
$xml .= '</categories>' . PHP_EOL;
$xml .= '<offers>' . PHP_EOL;

write($xml, whatFileTo($temp_file.$xml_id.'.xml'), 1);

$unload_products_rezult = $db->query("SELECT * FROM `oc_roz_unload_products`");
while( $unload_products_row = mysqli_fetch_assoc($unload_products_rezult) ) {
    time_nanosleep(0, 10000000);
    $xml = '';
    
    $offer_id = $unload_products_row['id'];
    $product_id = $unload_products_row['product_id'];
    $available = $unload_products_row['available'];
    $roz_category_id = $unload_products_row['roz_category_id'];
    $url = $unload_products_row['url'];
    $price = $unload_products_row['price'];
    
    $name = $unload_products_row['name'];
    
    $description = html_entity_decode($unload_products_row['description']);
    //$description = strip_tags($description, '<p><div><span><h3><h4><h5><ul><ol><li>');
    //$description = str_ireplace('<p></p>', ' ', $description);
    $description = str_ireplace('<p><br></p>', ' ', $description);
    
    $description = str_ireplace('<p >', '<p>', $description);
    $description = str_ireplace('<br >', '<br>', $description);
    
    $description = strip_tags($description, '<br><p>');
    $description = stripAttributes($description);
    
    $description = str_ireplace('<p >', '<p>', $description);
    $description = str_ireplace('<br >', '<br>', $description);
    
    //$description = str_ireplace('&nbsp;', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    $description = str_ireplace('  ', ' ', $description);
    
    $description = str_ireplace('body,div,table,thead,tbody,tfoot,tr,th,td,p { font-family:"Liberation Sans";', '', $description);
    $description = str_ireplace('font-size:x-small }', '', $description);
    $description = str_ireplace('a.comment-indicator:hover + comment { background:#ffd; position:absolute;', '', $description);
    $description = str_ireplace('display:block; border:1px solid black; padding:0.5em; }', '', $description);
    $description = str_ireplace('a.comment-indicator { background:red; display:inline-block; border:1px solid black;', '', $description);
    $description = str_ireplace('width:0.5em; height:0.5em; }', '', $description);
    $description = str_ireplace('comment { display:none; }', '', $description);
    
    $description__arr = explode('&nbsp;&nbsp;', $description);
    if($description__arr){
        $description = '';
        foreach ($description__arr as $description__str){
            if(!empty($description__str) and $description__str != ' '){
                $description .= $description__str;
            }
        }
    }
    
    $description__arr = explode('&nbsp; &nbsp;', $description);
    if($description__arr){
        $description = '';
        foreach ($description__arr as $description__str){
            if(!empty($description__str) and $description__str != ' '){
                $description .= $description__str;
            }
        }
    }
    
    $description = str_ireplace('&nbsp;', ' ', $description);
    
    $pictures = $unload_products_row['pictures'];
    $params = $unload_products_row['params'];
    $vendor = $unload_products_row['vendor'];
    $size_name = $unload_products_row['size_name'];
    $size_value = $unload_products_row['size_value'];
    $color = $unload_products_row['color'];
    $stock_quantity = $unload_products_row['stock_quantity'];
    $available = $unload_products_row['available'];
    
    $vendor_code = $product_id . '-' . $offer_id;
    
    if(!empty($size_value)){
        $name .=  " $size_value";
    }
    
    if(!empty($color)){
        $name .=  " $color";
    }
    
    if(!empty($vendor_code)){
        $name .=  " ($vendor_code)";
    }
    
    $xml .= '<offer id="'. $offer_id .'" available="' . $available . '">' . PHP_EOL;
    $xml .= '<url><![CDATA['. $url .']]></url>' . PHP_EOL;
    $xml .= '<price>' . $price . '</price>' . PHP_EOL;
    $xml .= '<currencyId>UAH</currencyId>' . PHP_EOL;
    $xml .= '<categoryId>' . $roz_category_id . '</categoryId>' . PHP_EOL;
    $xml .= $pictures . PHP_EOL;
    $xml .= '<delivery>true</delivery>' . PHP_EOL;
    $xml .= '<name><![CDATA[' . $name . ']]></name>' . PHP_EOL;
    $xml .= '<vendor><![CDATA[' . $vendor . ']]></vendor>' . PHP_EOL;
    $xml .= '<vendorCode>' . $vendor_code . '</vendorCode>' . PHP_EOL;
    $xml .= '<description><![CDATA[' . $description . ']]></description>' . PHP_EOL;
    if(!empty($size_name) and !empty($size_value)){
        $xml .= '<param name="'.$size_name.'">' . $size_value . '</param>' . PHP_EOL;
    }
    if($color){
        $xml .= '<param name="Цвет"><![CDATA[' . $color . ']]></param>' . PHP_EOL;
    }
    $xml .= $params . PHP_EOL;
    if($available == 'true'){
        $xml .= '<stock_quantity>' . $stock_quantity . '</stock_quantity>' . PHP_EOL;
    }else{
        $xml .= '<stock_quantity>0</stock_quantity>' . PHP_EOL;
    }
    $xml .= '</offer>' . PHP_EOL;
    
    write($xml, whatFileTo($temp_file.$xml_id.'.xml'), 1);
}

$xml = '';
$xml .= '</offers></shop></yml_catalog>' . PHP_EOL;
write($xml, whatFileTo($temp_file.$xml_id.'.xml'), 1);

writeFinalFile($final_file, $temp_file, $xml_id);

@unlink($on); file_put_contents($off, 'OFF!!!');
?>