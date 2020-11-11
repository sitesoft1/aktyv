<?php
set_time_limit(0);
/*
//Погасим ошибки
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
*/

//выводить все ошибки
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../../config.php';

define('DB_CHARSET', 'utf8');

//Load WP functions
//require_once(__DIR__ . '/../../wp-load.php');
//global $wpdb;

class Db
{
    
    public $db;
    
    public function __construct()
    {
        require_once __DIR__ . '/../../config.php';
        //DB CONNECT
        $this->db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        if ($this->db->connect_errno) {
            $err = "Не удалось подключиться к MySQL: (" . $this->db->connect_errno . ") " . $this->db->connect_error;
            $this->log('construct_log', $err, true);
        }else{
            echo "Подключение к базе прошло успешно!";
        }
        $this->db->set_charset(DB_CHARSET);
        //DB CONNECT END
        
        //$this->db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        //$this->config = new Config();
    }
    
    public function query($sql)
    {
        
        if (!$result = $this->db->query($sql)) {
            $err = "Не удалось выполнить запрос: $sql <br>";
            $err .= "Номер ошибки: " . $this->db->errno . "\n";
            $err .= "Ошибка: " . $this->db->error . "\n";
            //$this->log('query_log', $err, true);
            
            return false;
        }
        
        if ($result->num_rows > 0) {
            return $result;
        } else {
            $err = "Функция query по данным: <br> $sql <br> - mysql вернула пустой результат! <br><hr>";
            //$this->log('query_log', $err, true);
            
            return false;
        }
        
    }
    
    public function query_assoc($sql, $row_filed)
    {
        
        if (!$result = $this->db->query($sql)) {
            $err = "Не удалось выполнить запрос: $sql <br>";
            $err .= "Номер ошибки: " . $this->db->errno . "\n";
            $err .= "Ошибка: " . $this->db->error . "\n";
            //$this->log('query_assoc_log', $err, true);
            
            return false;
        }
        
        if ($result->num_rows > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row[$row_filed];
        } else {
            $err = "Функция query_assoc по данным: <br> $sql <br> $row_filed <br> - mysql вернула пустой результат! <br><hr>";
            //$this->log('query_assoc_log', $err, true);
            
            return false;
        }
        
    }
    
    public function query_insert($sql)
    {
        if (!$result = $this->db->query($sql)) {
            $err = "Не удалось выполнить запрос: (" . $this->db->errno . ") " . $this->db->error;
            $err .= "Номер ошибки: " . $this->db->errno . "\n";
            $err .= "Ошибка: " . $this->db->error . "\n";
            //$this->log('query_insert_log', $err, true);
            
            return false;
        }else{
            $err = "Запрос <br> $sql <br> - выполнен удачно! <br><hr>";
            //$this->log('query_insert_log', $err, true);
            
            return true;
        }
    
    }
    
    function query_insert_id($sql)
    {
        if (!$result = $this->db->query($sql)) {
            $err = "Не удалось выполнить запрос: (" . $this->db->errno . ") " . $this->db->error;
            $err .= "Номер ошибки: " . $this->db->errno . "\n";
            $err .= "Ошибка: " . $this->db->error . "\n";
            //$this->log('query_insert_id_log', $err, true);
            
            return false;
        }else{
            $err = "Запрос <br> $sql <br> - выполнен удачно! <br><hr>";
            //$this->log('query_insert_id_log', $err, true);
            
            return $this->db->insert_id;
            //return mysqli_insert_id($this->db);
        }
        
    }
    
    public function query_update($sql)
    {
        if (!$result = $this->db->query($sql)) {
            $err = "Не удалось выполнить запрос: (" . $this->db->errno . ") " . $this->db->error;
            $err .= "Номер ошибки: " . $this->db->errno . "\n";
            $err .= "Ошибка: " . $this->db->error . "\n";
            //$this->log('query_update_log', $err, true);
            
            return false;
        }else{
            $err = "Запрос <br> $sql <br> - выполнен удачно! <br><hr>";
            //$this->log('query_update_log', $err, true);
            
            return true;
        }
        
    }
    
    public function query_delete($sql)
    {
        if (!$result = $this->db->query($sql)) {
            $err = "Не удалось выполнить запрос: (" . $this->db->errno . ") " . $this->db->error;
            $err .= "Номер ошибки: " . $this->db->errno . "\n";
            $err .= "Ошибка: " . $this->db->error . "\n";
            //$this->log('query_delete_log', $err, true);
            
            return false;
        }else{
            $err = "Запрос <br> $sql <br> - выполнен удачно! <br><hr>";
            //$this->log('query_delete_log', $err, true);
            
            return true;
        }
        
    }
    
    public function log($filename, $data, $append=false)
    {
        if($append){
            file_put_contents(__DIR__ . '/../var/'.$filename.'.txt', print_r($data, true), FILE_APPEND);
        }else{
            file_put_contents(__DIR__ . '/../var/'.$filename.'.txt', print_r($data, true));
        }
        
    }
    
    public function errorLog($err){
        $time = date('H-i-s');
        $err = $time.' '.$err;
        file_put_contents( __DIR__ . '/../var/api_error_log.txt', $err.PHP_EOL, FILE_APPEND);
    }
    
    public function getCurlHeader($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => true));
    
        $header = explode("\n", curl_exec($curl));
        curl_close($curl);
    
        return $header;
    }
    
    public function createSlug($name){
        $name = (string) $name;
        $slug = translit($name);
        return (string) $slug;
    }
    
    public function productCountLog($filename, $msg){
        $time = date('H-i-s');
        file_put_contents(__DIR__ . '/../var/'.$filename.'.txt', $time.' : '.$msg.PHP_EOL, FILE_APPEND);
    }
    
    public function createShortDescription($offer_description)
    {
        $offer_description = strip_tags($offer_description);
        
        $short_description = stristr($offer_description, '. ', TRUE);
        if(!$short_description){
            $short_description = stristr($offer_description, '.&nbsp;', TRUE);
        }
        
        if(!$short_description){
            $short_description = $offer_description;
        }else{
            $short_description = $short_description.'.';
        }
        
        return $short_description;
    }
    
    public function createGroupIdBySku($offer_vendor_code)
    {
        $d1 = '-';
        if(strripos($offer_vendor_code, $d1)){
            $str_arr = explode($d1, $offer_vendor_code);
        
            if( count($str_arr) == 2){
                if( iconv_strlen($str_arr[0]) > 3){
                    return $str_arr[0];
                }
            }elseif( count($str_arr) > 2 ){
            
                array_pop($str_arr);
                return implode($d1, $str_arr);
            
            }
        }
    
        $d2 = '/';
        if(strripos($offer_vendor_code, $d2)){
            $str_arr = explode($d2, $offer_vendor_code);
        
            if( count($str_arr) == 2){
                if( iconv_strlen($str_arr[0]) > 3){
                    return $str_arr[0];
                }
            }elseif( count($str_arr) > 2 ){
            
                array_pop($str_arr);
                return implode($d2, $str_arr);
            
            }
        }

        
        $d3 = '_';
        if(strripos($offer_vendor_code, $d3)){
            $str_arr = explode($d3, $offer_vendor_code);
        
            if( count($str_arr) == 2){
                if( iconv_strlen($str_arr[0]) > 3){
                    return $str_arr[0];
                }
            }elseif( count($str_arr) > 2 ){
            
                array_pop($str_arr);
                return implode($d3, $str_arr);
            
            }
        }
    
        return false;
    }
    
    public function concatAttributes($attr1, $attr2){
        
        if(!empty($attr1)){
            foreach ($attr1 as $k =>$v){
                if( isset($attr2[$k]) ){
                    $v[] = $attr2[$k];
                    $attr2[$k] = $v;
                }
            }
        }
        
        return $attr2;
    }
    
}