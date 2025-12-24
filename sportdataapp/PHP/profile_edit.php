<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = 'localhost';

$link = mysqli_connect($host, $usr, $pwd);
if(!$link){
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');
mysqli_select_db($link, 'sportdata_db');

$errors = [];
$success = false;
$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];

// 現在のユーザー情報を取得
$sql = "SELECT * FROM login_tbl WHERE user_id = ? AND group_id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "ss", $user_id, $group_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if(!$user_data){
    header('Location: login.php');
    exit();
}

// Ajaxリクエストの判定
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// POSTデータ取得
$name = $_POST['name'] ?? $user_data['name'];
$dob = $_POST['dob'] ?? $user_data['dob'];
$height = $_POST['height'] ?? $user_data['height'];
$weight = $_POST['weight'] ?? $user_data['weight'];
$position = $_POST['position'] ?? $user_data['position'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$new_password_confirm = $_POST['new_password_confirm'] ?? '';

if(isset($_POST['update'])){
    // バリデーション
    if(empty($name)){
        $errors[] = '氏名を入力してください';
    }
    
    if(empty($dob)){
        $errors[] = '生年月日を入力してください';
    }
    
    if(empty($height) || $height <= 0){
        $errors[] = '正しい身長を入力してください';
    }
    
    if(empty($weight) || $weight <= 0){
        $errors[] = '正しい体重を入力してください';
    }
    
    if(empty($position)){
        $errors[] = 'ポジション/役職を入力してください';
    }
    
    // パスワード変更がある場合
    $password_update = false;
    if(!empty($new_password) || !empty($current_password)){
        if(empty($current_password)){
            $errors[] = '現在のパスワードを入力してください';
        } elseif(!password_verify($current_password, $user_data['password'])){
            $errors[] = '現在のパスワードが正しくありません';
        } elseif(empty($new_password)){
            $errors[] = '新しいパスワードを入力してください';
        } elseif(strlen($new_password) < 6){
            $errors[] = '新しいパスワードは6文字以上で入力してください';
        } elseif($new_password !== $new_password_confirm){
            $errors[] = '新しいパスワードが一致しません';
        } else {
            $password_update = true;
        }
    }
    
    // エラーがなければ更新処理
    if(empty($errors)){
        if($password_update){
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE login_tbl SET name = ?, dob = ?, height = ?, weight = ?, position = ?, password = ? WHERE user_id = ? AND group_id = ?";
            $update_stmt = mysqli_prepare($link, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ssddssss", $name, $dob, $height, $weight, $position, $hash, $user_id, $group_id);
        } else {
            $update_sql = "UPDATE login_tbl SET name = ?, dob = ?, height = ?, weight = ?, position = ? WHERE user_id = ? AND group_id = ?";
            $update_stmt = mysqli_prepare($link, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ssddsss", $name, $dob, $height, $weight, $position, $user_id, $group_id);
        }
        
        if(mysqli_stmt_execute($update_stmt)){
            $success = true;
            
            // セッション情報を更新
            $_SESSION['name'] = $name;
            $_SESSION['dob'] = $dob;
            $_SESSION['height'] = $height;
            $_SESSION['weight'] = $weight;
            $_SESSION['position'] = $position;
            
            // データを再取得
            $user_data['name'] = $name;
            $user_data['dob'] = $dob;
            $user_data['height'] = $height;
            $user_data['weight'] = $weight;
            $user_data['position'] = $position;
            
            if($isAjax){
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => '登録情報を更新しました'
                ]);
                exit();
            }
        } else {
            $errors[] = "更新に失敗しました: " . mysqli_error($link);
        }
        mysqli_stmt_close($update_stmt);
    }
    
    if($isAjax && !empty($errors)){
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit();
    }
}

$NAV_BASE = '.';
$showLoader = false;

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/profile_edit.html.php';
