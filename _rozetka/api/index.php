<?php
//baza

require_once __DIR__ . '/../../config.php';
$mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}else{
    echo "Подключение к базе прошло успешно!";
}
// Zadaem kodirovku poluchennogo iz bazi
	$result1 = $mysqli->query("SET NAMES utf8;");
	$result2 = $mysqli->query("SET character_set_client = utf8;");
	$result3 = $mysqli->query("SET character_set_connection = utf8;");
	$result4 = $mysqli->query("SET character_set_results = utf8;");
// Zadaem kodirovku poluchennogo iz bazi END
//Ochistim tablicu
//$clean_table = $mysqli->query("DELETE FROM _roz_categories");//RASKOMENTIT DLA ZAPUSKA
//baza END



$token_path = __DIR__ . '/token.txt';
$cat_path = __DIR__ . '/categories.xml';
$PostUrl = 'https://api.seller.rozetka.com.ua/sites';
$PostData = array(
					'username' => 'urbanshop',
					'password' => base64_encode('z2rpkxg4ml40')
				);
echo '<pre>';
				
				
$token = file_get_contents($token_path);
//var_dump($token);

function GetInfo($token, $url){
    $opts = array('http' =>
        array(
            'method'  => 'GET',
            'header'  => 'Authorization: Bearer ' . $token,
        )
    );
    $context  = stream_context_create($opts);

    $rezult = file_get_contents($url, false, $context);
	$rezult = json_decode($rezult);
	
	//echo '<pre>';
	//var_dump($rezult);
	//echo '<br><hr>';
	
	return $rezult;
}

function GetToken($PostUrl, $PostData, $token_path){
		$PostData = http_build_query($PostData);// massiv v url dla zaprosa
	//curl POST zapros
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_URL, $PostUrl);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $PostData);
		$PostRezult = curl_exec($curl);
		curl_close($curl);//zakrili curl
	//curl POST zapros END
	echo '<pre>';
	var_dump($PostRezult);
	echo '<br><hr>';

	$rezult = json_decode($PostRezult);
	//var_dump($rezult);
	//echo '<br><hr>';

	echo $rezult->content->access_token;
	$token = $rezult->content->access_token;
	//Zapishem token
	file_put_contents($token_path, $token);
	echo '<br><hr>';

	return $token;
}

if(!$token){
	$token = GetToken($PostUrl, $PostData, $token_path);
}




function WriteCategories($cats){
	global $cat_path;
	global $mysqli;
	foreach($cats as $cat){
		$str = $cat['category_id'] . ' | ' . $cat['name'] .  ' | ' . $cat['parent_id'];
		file_put_contents($cat_path, $str . PHP_EOL, FILE_APPEND);
		//Zapishem v bazu
		$category_id = $cat['category_id'];
		$name = $cat['name'];
		$parent_id = $cat['parent_id'];
		$rezult = $mysqli->query("INSERT INTO oc_roz_categories (category_id, name, parent_id) VALUES ('$category_id','$name','$parent_id')");
	}
}


function GetChildCategories($token, $cat_path, $parent_cats){
	time_nanosleep(0, 500000000);
	//zapishem poluchennie categorii
	//file_put_contents($cat_path, $parent_cats, FILE_APPEND);
    WriteCategories($parent_cats);
	//$rozetka_cats = $parent_cats;//suda budem pisat vse kategirii
	$child_cats = [];//massiv dochernih kategoriy
	
	foreach($parent_cats as $parent_cat){
		
		$cat_info = GetInfo($token, 'https://api.seller.rozetka.com.ua/market-categories/search?parent_id='.$parent_cat['category_id']);
		//var_dump($parent_cat);
		//var_dump($cat_info);
		$operating_cats = $cat_info->content->marketCategorys;//esli dochki est to > 0
		var_dump($operating_cats);
		
		if( count($operating_cats) > 0 ){
			foreach($operating_cats as $op_c){
				$child_cats[] = array(
					'category_id' => $op_c->category_id,
					'name' => $op_c->name,
					'parent_id' => $op_c->parent_id
				);
			}//end foreach
		}//end if
		
	}//END foreach
	
	var_dump($child_cats);
	echo '<br><hr>';
	
	if( count($child_cats) > 0 ){
		GetChildCategories($token, $cat_path, $child_cats);
	}//end if
	
}//end function GetChildCategories


$parent_cats = array(
	['category_id'=>'1162030','name'=>'Одежда, обувь и аксессуары', 'parent_id' => NULL],
	['category_id'=>'4627893','name'=>'Спорт и увлечения', 'parent_id' => NULL],
	['category_id'=>'88468','name'=>'Товары для детей', 'parent_id' => NULL],
);

GetChildCategories($token, $cat_path, $parent_cats);//RASKOMENTIT DLA ZAPUSKA

?>