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

if(isset($_POST['send'])){
    if($group_id === '' || $user_id === '' || $password === ''){
        echo '入力内容を確認して下さい';
    }else{
        $sql = "SELECT * FROM login_tbl WHERE group_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($link, $sql);
        if(!$stmt){
            die('ステートメント準備に失敗しました。'. mysqli_error($link));
        }
        mysqli_stmt_bind_param($stmt, "ss", $group_id, $user_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if($row = mysqli_fetch_assoc($result)){
            if(password_verify($password, $row['password'])){
                session_regenerate_id(true);

                $_SESSION['group_id'] = $row['group_id'];
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['dob'] = $row['dob'];
                $_SESSION['height'] = $row['height'];
                $_SESSION['weight'] = $row['weight'];
                $_SESSION['position'] = $row['position'];
                $_SESSION['show_loader'] = true;
                $_SESSION['first_login'] = true;

                header('Location: home.php');
                exit();
            }else{
                echo 'パスワードが間違っています。'; 
            }
        }else{
            echo 'ユーザーが登録されていません';
        }
    mysqli_stmt_close($stmt);
    }
}

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/login.html.php';
