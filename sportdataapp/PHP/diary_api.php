<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    echo json_encode(['success' => false, 'message' => '認証エラー']);
    exit;
}

$dbHost = 'localhost';
$dbUser = 'y24514';
$dbPass = 'Kr96main0303';
$dbName = 'sportdata_db';

$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    echo json_encode(['success' => false, 'message' => 'DB接続エラー']);
    exit;
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'save':
        $id = $_POST['id'] ?? null;
        $diary_date = $_POST['diary_date'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        
        if (empty($diary_date) || empty($content)) {
            echo json_encode(['success' => false, 'message' => '日付と内容は必須です']);
            exit;
        }
        
        if ($id) {
            // 更新
            $stmt = mysqli_prepare($link, "UPDATE diary_tbl SET diary_date = ?, title = ?, content = ?, tags = ?, updated_at = NOW() WHERE id = ? AND user_id = ? AND group_id = ?");
            mysqli_stmt_bind_param($stmt, "sssssss", $diary_date, $title, $content, $tags, $id, $user_id, $group_id);
        } else {
            // 新規作成
            $stmt = mysqli_prepare($link, "INSERT INTO diary_tbl (group_id, user_id, diary_date, title, content, tags, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            mysqli_stmt_bind_param($stmt, "ssssss", $group_id, $user_id, $diary_date, $title, $content, $tags);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => $id ? '更新しました' : '保存しました']);
        } else {
            echo json_encode(['success' => false, 'message' => 'エラーが発生しました']);
        }
        mysqli_stmt_close($stmt);
        break;
        
    case 'delete':
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'IDが指定されていません']);
            exit;
        }
        
        $stmt = mysqli_prepare($link, "DELETE FROM diary_tbl WHERE id = ? AND user_id = ? AND group_id = ?");
        mysqli_stmt_bind_param($stmt, "sss", $id, $user_id, $group_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => '削除しました']);
        } else {
            echo json_encode(['success' => false, 'message' => '削除に失敗しました']);
        }
        mysqli_stmt_close($stmt);
        break;
        
    case 'get':
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'IDが指定されていません']);
            exit;
        }
        
        $stmt = mysqli_prepare($link, "SELECT id, diary_date, title, content, tags FROM diary_tbl WHERE id = ? AND user_id = ? AND group_id = ?");
        mysqli_stmt_bind_param($stmt, "sss", $id, $user_id, $group_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => '日記が見つかりません']);
        }
        mysqli_stmt_close($stmt);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => '不正なアクション']);
        break;
}

mysqli_close($link);
?>
