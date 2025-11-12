<?php
session_start();

$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = '127.0.0.1';
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
    $created_at = "NOW()";

    $stmt = mysqli_prepare($link, "INSERT INTO pi_tbl(group_id, user_id, height, weight, injury, sleep_time, created_at ) VALUES(?, ?, ?, ?, ?, ?, ?) ");
    mysqli_stmt_bind_param($stmt, "ssddsss", $group_id, $user_id, $height, $weight, $injury, $sleep_time, $created_at);

}


?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ホームページ</title>
    <link rel="stylesheet" href="../css/pi.css">
</head>
<body>
    <div class="container">
        <div class="meny">
            <button class="meny-btn"></button>
            <nav class="meny-nav">
                <ul>
                    <li><a href="home.php">ホーム</a></li>
                    <li><a href="pi.php">身体情報</a></li>
                </ul>
            </nav>
        </div> 
        <div class="input-panel">
            <form action="" method="post">
                <h3>データ入力</h3>
                <label for="height">身長</label>
                <input type="number" id="height" name="height" placeholder="cm" min="50" max="250" step="0.1"><br>

                <label for="weight">体重</label>
                <input type="number" id="weight" name="weight" placeholder="kg" min="0" max="300" step="0.1"><br>

                <label for="injury">怪我履歴</label>
                <input type="text" id="injury" name="injury"><br>

                <label for="sleep_time">睡眠時間</label>
                <input type="time" id="sleep_time" name="sleep_time" value="05:00" step="900"><br>

                <input type="submit" value="送信">
            </form>
        </div>
            <div class="graph-panel">
                <div class="graph-panel_1">
                    <h2>身長・体重・BMI</h2>
                    <canvas class="graph height-weight-bmi"></canvas>
                </div>
                <div class="graph-panel_2">
                <div class="panel-item">
                    <h2>睡眠時間</h2>
                    <canvas class="graph sleep"></canvas>
                </div>
                <div class="panel-item">
                    <h2>怪我履歴</h2>
                    <canvas class="graph injury"></canvas>
                </div>
            </div>
        </div>
    </div>
    <script src=../js/meny.js></script>
    <script src=../js/chart_pi.js></script>
</body>
</html>
