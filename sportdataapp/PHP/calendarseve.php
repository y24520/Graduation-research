<?php

use Dom\childNode;
session_start();

$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = '127.0.0.1';
$dbName = 'sportdate_db';

$link = mysqli_connect($host,$usr,$pwd,$dbName);
if(!$link){
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];


?>