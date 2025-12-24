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

$errors = [];
$success = false;

// Ajaxリクエストの判定
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// POSTデータ取得
$group_id = $_POST['group_id'] ?? '';
$user_id = $_POST['user_id'] ?? '';
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$name = $_POST['name'] ?? '';
$dob = $_POST['dob'] ?? '';
$height = $_POST['height'] ?? '';
$weight = $_POST['weight'] ?? '';
$position = $_POST['position'] ?? '';

if(isset($_POST['reg'])){
    // バリデーション
    if(empty($group_id)){
        $errors[] = '団体IDを入力してください';
    }
    
    if(empty($user_id)){
        $errors[] = 'ユーザーIDを入力してください';
    } elseif(strlen($user_id) < 4){
        $errors[] = 'ユーザーIDは4文字以上で入力してください';
    } else {
        // ユーザーID重複チェック
        $check_sql = "SELECT user_id FROM login_tbl WHERE user_id = ?";
        $check_stmt = mysqli_prepare($link, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $user_id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        if(mysqli_stmt_num_rows($check_stmt) > 0){
            $errors[] = 'このユーザーIDは既に使用されています';
        }
        mysqli_stmt_close($check_stmt);
    }
    
    if(empty($password)){
        $errors[] = 'パスワードを入力してください';
    } elseif(strlen($password) < 6){
        $errors[] = 'パスワードは6文字以上で入力してください';
    } elseif($password !== $password_confirm){
        $errors[] = 'パスワードが一致しません';
    }
    
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
    
    // エラーがなければ登録処理
    if(empty($errors)){
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO login_tbl (group_id, user_id, password, name, dob, height, weight, position) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        if(!$stmt){
            $errors[] = "データベースエラー: " . mysqli_error($link);
        } else {
            mysqli_stmt_bind_param($stmt, "sssssdds", $group_id, $user_id, $hash, $name, $dob, $height, $weight, $position);

            if (mysqli_stmt_execute($stmt)) {
                $success = true;
                $_SESSION['registration_success'] = true;
                mysqli_stmt_close($stmt);
                
                // Ajaxリクエストの場合はJSONを返す
                if($isAjax){
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => '登録が完了しました！',
                        'redirect' => 'login.php'
                    ]);
                    exit();
                } else {
                    header('Location: login.php');
                    exit();
                }
            } else {
                $errors[] = "登録に失敗しました: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Ajaxリクエストでエラーがある場合
    if($isAjax && !empty($errors)){
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit();
    }
}


// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/reg.html.php';
