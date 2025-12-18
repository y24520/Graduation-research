<?php
session_start();

$link = mysqli_connect("localhost", "y24514", "Kr96main0303", "sportdata_db");
mysqli_set_charset($link, "utf8");

$group_id = $_SESSION['group_id'];
$user_id  = $_SESSION['user_id'];

$showSuccess = isset($_GET['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ===== 基本情報 ===== */
    $swim_date  = $_POST['swim_date'];
    $condition  = (int)$_POST['condition'];
    $memo       = $_POST['memo'];

    $pool       = $_POST['pool'];
    $event      = $_POST['event'];
    $distance   = (int)$_POST['distance'];
    $total_time = (float)$_POST['total_time'];

    /* ===== ストローク ===== */
    $stroke_data = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'stroke_') === 0) {
            $stroke_data[$key] = (int)$value;
        }
    }

    /* ===== ラップ ===== */
    $lap_data = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'lap_time_') === 0) {
            $lap_data[$key] = $value;
        }
    }

    /* ===== INSERT ===== */
    $sql = "
        INSERT INTO swim_tbl (
            group_id,
            user_id,
            swim_date,
            meet_name,
            round,
            `condition`,
            session_type,
            pool,
            event,
            distance,
            total_time,
            stroke_json,
            lap_json,
            memo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "sssssisssidsss",
        $group_id,
        $user_id,
        $swim_date,
        $meet_name,
        $round,
        $condition,
        $session_type,
        $pool,
        $event,
        $distance,
        $total_time,
        json_encode($stroke_data, JSON_UNESCAPED_UNICODE),
        json_encode($lap_data, JSON_UNESCAPED_UNICODE),
        $memo
    );

    mysqli_stmt_execute($stmt);

    header("Location: swim_input.php?success=1");
    exit;
}

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../../HTML/swim_input.html.php';
