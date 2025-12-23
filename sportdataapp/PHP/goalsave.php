<?php
session_start();

if(!isset($_SESSION['user_id'], $_SESSION['group_id'])){
    header('Location: login.php');
    exit();
}

$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = 'localhost';
$dbName = 'sportdata_db';

$link = mysqli_connect($host, $usr, $pwd, $dbName);
if(!$link){
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link,'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$corrent_goal = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
header('Content-Type: application/json');

$goal = $_POST['goal'] ?? '';

if (empty(trim($goal))) {
    echo json_encode(['success' => false, 'message' => '目標を入力してください']);
    exit();
}

$stmt2 = mysqli_prepare($link, "SELECT * FROM goal_tbl WHERE user_id = ? AND group_id = ?");
mysqli_stmt_bind_param($stmt2, "ss", $user_id , $group_id);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);

if(mysqli_num_rows($result2) > 0){
    $stmt2 = mysqli_prepare($link, "UPDATE goal_tbl SET goal = ? ,created_at = NOW() WHERE user_id = ? AND group_id = ?");
    mysqli_stmt_bind_param($stmt2, "sss", $goal, $user_id, $group_id);
    $success = mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
}else{
    $stmt3 = mysqli_prepare($link,"INSERT INTO goal_tbl(group_id,user_id,goal,created_at)VALUES(?, ?, ?,NOW())");
    mysqli_stmt_bind_param($stmt3, "sss", $group_id, $user_id, $goal);
    $success = mysqli_stmt_execute($stmt3);
    mysqli_stmt_close($stmt3);
}

mysqli_close($link);

if($success){
    echo json_encode(['success' => true, 'message' => '目標を保存しました']);
}else{
    echo json_encode(['success' => false, 'message' => '保存に失敗しました']);
}
exit();
}

?>