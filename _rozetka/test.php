<?php
$user="This is not Here.";

if ( mb_detect_encoding($user) != "ASCII" ) {
    echo "есть символы, отличные от ASCII, возможно - кириллица";
} else {
    echo "ASCII текст";
}