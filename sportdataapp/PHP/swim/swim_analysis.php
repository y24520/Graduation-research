<?php
session_start();

/* ---------------------
   セッションチェック
--------------------- */
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    header('Location: ../login.php');
    exit;
}

$group_id = $_SESSION['group_id'];
$user_id  = $_SESSION['user_id'];

/* =====================
   DB接続 (環境変数優先)
   実運用では .env などで環境変数を設定してください
===================== */
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'y24514';
$dbPass = getenv('DB_PASS') ?: 'Kr96main0303';
$dbName = getenv('DB_NAME') ?: 'sportdata_db';

$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    error_log('DB connect error: ' . mysqli_connect_error());
    http_response_code(500);
    echo 'データベース接続に失敗しました。';
    exit;
}
mysqli_set_charset($link, "utf8");

// GET パラメータでの選択受け取り (combo=pool|event|distance を想定)
$selected_combo = $_GET['combo'] ?? null;
$sel_pool = null; $sel_event = null; $sel_distance = null;
if ($selected_combo) {
    $parts = explode('|', $selected_combo);
    if (count($parts) === 3) {
        $sel_pool = $parts[0];
        $sel_event = $parts[1];
        $sel_distance = $parts[2];
    }
}

/* 種目一覧を取得（ドロップダウン用） */
$combo_sql = "SELECT pool, event, distance FROM swim_tbl WHERE group_id=? AND user_id=? GROUP BY pool, event, distance ORDER BY pool, event, distance";
$combo_stmt = mysqli_prepare($link, $combo_sql);
$combos = [];
if ($combo_stmt) {
    mysqli_stmt_bind_param($combo_stmt, "ss", $group_id, $user_id);
    if (mysqli_stmt_execute($combo_stmt)) {
        $r = mysqli_stmt_get_result($combo_stmt);
        while ($row = mysqli_fetch_assoc($r)) {
            $combos[] = $row;
        }
    } else {
        error_log('Execute failed (combos): ' . mysqli_stmt_error($combo_stmt));
    }
    mysqli_stmt_close($combo_stmt);
}

/* =====================
   最新記録（今回）
===================== */

// 選択された種目があればその種目の最新記録を取得、なければ全体の最新を取得
if ($sel_pool && $sel_event && $sel_distance) {
    $sql = "
        SELECT pool, event, distance, total_time
        FROM swim_tbl
        WHERE group_id=? AND user_id=?
          AND pool=? AND event=? AND distance=?
        ORDER BY created_at DESC
        LIMIT 1
    ";
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        error_log('Prepare failed (current filtered): ' . mysqli_error($link) . ' SQL: ' . $sql);
        http_response_code(500);
        echo 'システムエラーが発生しました。';
        exit;
    }
    $d_param = is_numeric($sel_distance) ? (int)$sel_distance : $sel_distance;
    mysqli_stmt_bind_param($stmt, "ssssi", $group_id, $user_id, $sel_pool, $sel_event, $d_param);
} else {
    $sql = "
        SELECT pool, event, distance, total_time
        FROM swim_tbl
        WHERE group_id=? AND user_id=?
        ORDER BY created_at DESC
        LIMIT 1
    ";
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        error_log('Prepare failed (current): ' . mysqli_error($link) . ' SQL: ' . $sql);
        http_response_code(500);
        echo 'システムエラーが発生しました。';
        exit;
    }
    mysqli_stmt_bind_param($stmt, "ss", $group_id, $user_id);
}

if (!mysqli_stmt_execute($stmt)) {
    error_log('Execute failed (current): ' . mysqli_stmt_error($stmt));
    echo 'システムエラーが発生しました。';
    exit;
}
$res = mysqli_stmt_get_result($stmt);
$current = mysqli_fetch_assoc($res);

if (!$current) {
    echo "記録がありません";
    exit;
}

$pool     = $current['pool'];
$event    = $current['event'];
$distance = $current['distance'];
$now_time = $current['total_time'];

// total_time が文字列なら秒に変換するヘルパー
function parse_time_to_seconds($t) {
    if ($t === null || $t === '') return null;
    if (is_numeric($t)) return (float)$t;
    $t = trim($t);
    if (strpos($t, ':') !== false) {
        list($m, $s) = explode(':', $t, 2);
        $m = intval($m);
        $s = floatval($s);
        return $m * 60 + $s;
    }
    return floatval($t);
}

$now_time = parse_time_to_seconds($now_time);

/* =====================
   前回記録（同条件）
===================== */
$sql = "
    SELECT total_time
    FROM swim_tbl
    WHERE group_id=? AND user_id=?
      AND pool=? AND event=? AND distance=?
    ORDER BY created_at DESC
    LIMIT 1 OFFSET 1
";
$stmt = mysqli_prepare($link, $sql);
if (!$stmt) {
    error_log('Prepare failed (prev): ' . mysqli_error($link) . ' SQL: ' . $sql);
    $prev_time = null;
} else {
    $d_param = is_numeric($distance) ? (int)$distance : $distance;
    mysqli_stmt_bind_param($stmt, "ssssi", $group_id, $user_id, $pool, $event, $d_param);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Execute failed (prev): ' . mysqli_stmt_error($stmt));
        $prev_time = null;
    } else {
        $res = mysqli_stmt_get_result($stmt);
        $prev = mysqli_fetch_assoc($res);
        $prev_time = $prev['total_time'] ?? null;
        $prev_time = parse_time_to_seconds($prev_time);
    }
}

/* =====================
   自己ベスト
===================== */
$sql = "
    SELECT best_time
    FROM swim_best_tbl
    WHERE group_id=? AND user_id=?
      AND pool=? AND event=? AND distance=?
    LIMIT 1
";
$stmt = mysqli_prepare($link, $sql);
if (!$stmt) {
    error_log('Prepare failed (best): ' . mysqli_error($link) . ' SQL: ' . $sql);
    $best_time = null;
} else {
    $d_param = is_numeric($distance) ? (int)$distance : $distance;
    mysqli_stmt_bind_param($stmt, "ssssi", $group_id, $user_id, $pool, $event, $d_param);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Execute failed (best): ' . mysqli_stmt_error($stmt));
        $best_time = null;
    } else {
        $res = mysqli_stmt_get_result($stmt);
        $best = mysqli_fetch_assoc($res);
        $best_time = $best['best_time'] ?? null;
        $best_time = parse_time_to_seconds($best_time);
    }
}

/* =====================
   推移データ
===================== */
$sql = "
    SELECT swim_date, total_time
    FROM swim_tbl
    WHERE group_id=? AND user_id=?
      AND pool=? AND event=? AND distance=?
    ORDER BY swim_date ASC, created_at ASC
";
$stmt = mysqli_prepare($link, $sql);
if (!$stmt) {
    error_log('Prepare failed (history): ' . mysqli_error($link) . ' SQL: ' . $sql);
    $history = [];
} else {
    $d_param = is_numeric($distance) ? (int)$distance : $distance;
    mysqli_stmt_bind_param($stmt, "ssssi", $group_id, $user_id, $pool, $event, $d_param);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Execute failed (history): ' . mysqli_stmt_error($stmt));
        $history = [];
    } else {
        $res = mysqli_stmt_get_result($stmt);
        $history = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $row['total_time'] = parse_time_to_seconds($row['total_time']);
            $history[] = $row;
        }
    }
}

if (isset($stmt) && $stmt) mysqli_stmt_close($stmt);
mysqli_close($link);

/* 種目名変換 */
$event_map = [
    "fly" => "バタフライ",
    "ba"  => "背泳ぎ",
    "br"  => "平泳ぎ",
    "fr"  => "自由形",
    "im"  => "個人メドレー"
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>水泳｜分析</title>
<link rel="stylesheet" href="../../css/swim.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- nav スタイルは外部 css/nav.css に移動しました -->
</head>
<body>

<?php $NAV_BASE = '..'; require_once __DIR__ . '/../header.php'; ?>

<!-- 種目選択フォーム -->
<form method="get" style="margin-bottom:1em;">
    <label for="combo">種目を選択：</label>
    <select id="combo" name="combo">
        <option value="">（最新の記録を表示）</option>
        <?php foreach ($combos as $c):
            $val = htmlspecialchars($c['pool'] . '|' . $c['event'] . '|' . $c['distance'], ENT_QUOTES, 'UTF-8');
            $label = ($c['pool'] === 'short' ? '短水路' : '長水路') . ' ' . htmlspecialchars($c['distance'], ENT_QUOTES, 'UTF-8') . 'm ' . htmlspecialchars($event_map[$c['event']] ?? $c['event'], ENT_QUOTES, 'UTF-8');
            $sel = ($selected_combo && $selected_combo === ($c['pool'] . '|' . $c['event'] . '|' . $c['distance'])) ? ' selected' : '';
        ?>
            <option value="<?= $val ?>"<?= $sel ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">表示</button>
</form>

<h2>
<?= htmlspecialchars($pool === "short" ? "短水路" : "長水路", ENT_QUOTES, 'UTF-8') ?>
<?= htmlspecialchars($distance, ENT_QUOTES, 'UTF-8') ?>m
<?= htmlspecialchars($event_map[$event] ?? $event, ENT_QUOTES, 'UTF-8') ?>
</h2>

<!-- 比較テーブル（3列） -->
<div class="layout-two-col">
    <div class="left-col">
        <table border="1">
    <caption>今回の記録と比較</caption>
    <tr>
        <th></th>
        <th scope="col">前回</th>
        <th scope="col">ベスト</th>
    </tr>
    <tr>
        <th>今回</th>
        <td id="prev-now"></td>
        <td id="best-now"></td>
    </tr>
    <tr>
        <th>比較対象</th>
        <td id="prev-then"></td>
        <td id="best-then"></td>
    </tr>
    <tr>
        <th>差分</th>
        <td id="diff-prev"></td>
        <td id="diff-best"></td>
    </tr>
        </table>

        <br>
        <!-- 比較表は左のまま。小さな比較グラフは右カラムへ移動しました -->
    </div>

    <div class="right-col">
        <!-- 推移グラフ -->
        <div class="chart-container">
            <canvas id="timeChart" height="320"></canvas>
        </div>

        <!-- 比較チャート（前回 vs 今回, ベスト vs 今回）: 右カラムに表示 -->
        <div class="compare-container">
            <div class="compare-chart">
                <h3>前回 vs 今回</h3>
                <canvas id="prevNowChart" height="200"></canvas>
            </div>
            <div class="compare-chart">
                <h3>ベスト vs 今回</h3>
                <canvas id="bestNowChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- =====================
     PHP → JS データ渡し
===================== -->
<script>
const NOW_TIME  = <?= json_encode($now_time, JSON_UNESCAPED_UNICODE) ?>;
const PREV_TIME = <?= json_encode($prev_time, JSON_UNESCAPED_UNICODE) ?>;
const BEST_TIME = <?= json_encode($best_time, JSON_UNESCAPED_UNICODE) ?>;
const HISTORY   = <?= json_encode($history, JSON_UNESCAPED_UNICODE) ?>;
</script>

<!-- JS 別ファイル -->
<script src="../../js/swim/swim_analysis.js"></script>
</body>
</html>
