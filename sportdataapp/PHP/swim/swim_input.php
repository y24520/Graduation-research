<?php
session_start();

$link = mysqli_connect("localhost", "y24514", "Kr96main0303", "sportdata_db");
mysqli_set_charset($link, "utf8");

$group_id = $_SESSION['group_id'];
$user_id  = $_SESSION['user_id'];

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
            condition,
            pool,
            event,
            distance,
            total_time,
            stroke_json,
            lap_json,
            memo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "sssissidsss",
        $group_id,
        $user_id,
        $swim_date,
        $condition,
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
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>水泳｜記録</title>
    <link rel="stylesheet" href="../../css/swim.css">
    <link rel="stylesheet" href="../../css/site.css">
    
</head>
<body>
<?php $NAV_BASE = '..'; require_once __DIR__ . '/../header.php'; ?>

<div class="top">
    <div class="input-form">

        <?php if (isset($_GET['success'])): ?>
            <p class="success">記録を保存しました</p>
        <?php endif; ?>

        <form method="post" id="swim-form">

            <label>日付</label>
            <input type="date" name="swim_date" required><br>

            <label>大会名</label>
            <input type="text" name="meet_name" placeholder="大会名"><br>

            <label>ラウンド</label>
            <select name="round">
                <option value="予選">予選</option>
                <option value="準決勝">準決勝</option>
                <option value="決勝">決勝</option>
                <option value="タイム決勝">タイム決勝</option>
            </select><br>

            <label>体調</label>
            <select name="condition">
                <option value="5">とても良い</option>
                <option value="4">良い</option>
                <option value="3">普通</option>
                <option value="2">悪い</option>
                <option value="1">とても悪い</option>
            </select><br>

            <label>メモ</label>
            <textarea name="memo" rows="3"></textarea><br>

            <label>プール</label>
            <select id="pool_type" name="pool" required>
                <option value="" disabled selected>選択してください</option>
                <option value="short">短水路</option>
                <option value="long">長水路</option>
            </select><br>

            <label>種目</label>
            <select id="event" name="event" required>
                <option value="" disabled selected>選択してください</option>
                <option value="fly">バタフライ</option>
                <option value="ba">背泳ぎ</option>
                <option value="br">平泳ぎ</option>
                <option value="fr">自由形</option>
                <option value="im">個人メドレー</option>
            </select><br>

            <label>距離</label>
            <select id="distance" name="distance" required>
                <option value="" disabled selected>選択してください</option>
                <option value="25">25m</option>
                <option value="50">50m</option>
                <option value="100">100m</option>
                <option value="200">200m</option>
                <option value="400">400m</option>
                <option value="800">800m</option>
                <option value="1500">1500m</option>
            </select><br>

            <input type="text" id="time" readonly>
            <input type="hidden" id="total_time" name="total_time">

            <!-- ストローク -->
            <div id="stroke_area">
                <label>ストローク回数</label>
                <input type="number" name="stroke_25" min="0" max="200" required>
            </div>

            <!-- ラップ -->
            <div id="lap_time_area">
                <label>ラップタイム</label>
                <input type="text"
                       name="lap_time_25"
                       placeholder="例: 15.23"
                       pattern="\d{1,2}\.\d{1,2}"
                       required>
            </div>

            <input type="submit" value="保存">
        </form>

    </div>
</div>

<script src="../../js/swim/swim_input.js"></script>
</body>
</html>
