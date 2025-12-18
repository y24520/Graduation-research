<?php
session_start();

$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = 'localhost';
$dbName = 'sportdata_db';

$link = mysqli_connect($host, $usr, $pwd, $dbName);
if(!$link){
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $injury = $_POST['injury'];
    $sleep_time = $_POST['sleep_time'];
    $create_at = "";

    $stmt = mysqli_prepare($link, "INSERT INTO pi_tbl(group_id, user_id, height, weight, injury, sleeptime, create_at ) VALUES(?, ?, ?, ?, ?, ?, NOw()) ");
    mysqli_stmt_bind_param($stmt, "ssddss", $group_id, $user_id, $height, $weight, $injury, $sleep_time);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();

    if($success){
        echo "身体データを登録しました。";
    } else{
        echo "エラー" . mysqli_error($link);
    }
}

$stmt2 = mysqli_prepare($link, "SELECT height, weight, injury, sleeptime, create_at FROM pi_tbl WHERE user_id = ? AND group_id = ?");
mysqli_stmt_bind_param($stmt2, "ss", $user_id, $group_id);
mysqli_stmt_execute($stmt2);
mysqli_stmt_bind_result($stmt2, $height, $weight, $injury, $sleep_time, $create_at);

$records = [];

while(mysqli_stmt_fetch($stmt2)){
    $records [] = [
        'height' => $height,
        'weight' => $weight,
        'injury' => $injury,
        'sleep_time' => $sleep_time,
        'create_at' => $create_at
    ];
}
mysqli_stmt_close($stmt2);

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/pi.html.php';
