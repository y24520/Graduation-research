<?php
session_start();

$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = 'localhost';

$link = mysqli_connect($host, $usr, $pwd);
if(!$link){
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');
mysqli_select_db($link, 'sportdata_db');

$group_id = $_POST['group_id'] ?? '';
$user_id = $_POST['user_id'] ?? '';
$password = $_POST['password'] ?? '';
$name = $_POST['name'] ?? '';
$dob = $_POST['dob'] ?? '';
$height = $_POST['height'] ?? '';
$weight = $_POST['weight'] ?? '';
$position = $_POST['position'] ?? '';

if(isset($_POST['reg'])){
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO login_tbl (group_id, user_id, password, name, dob, height, weight ,position) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $sql);
    if(!$stmt){
        die("ステートメント準備に失敗しました。". mysqli_error($link));
    }
    mysqli_stmt_bind_param($stmt, "sssssdds", $group_id, $user_id,  $hash, $name, $dob, $height, $weight, $position);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: login.php');
        exit();
    } else {
        echo "❌ 登録に失敗しました: " . mysqli_error($link);
    }

    mysqli_stmt_close($stmt);
}

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/reg.html.php';
