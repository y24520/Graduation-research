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
$now_raw  = $current['total_time'];

// total_time が文字列なら秒に変換するヘルパー
function parse_time_to_seconds($t) {
    if ($t === null || $t === '') return null;
    if (is_numeric($t)) return (float)$t;
    $t = trim($t);
    if (strpos($t, ':') !== false) {
        $parts = explode(':', $t);
        // h:mm:ss
        if (count($parts) === 3) {
            $h = intval($parts[0]);
            $m = intval($parts[1]);
            $s = floatval($parts[2]);
            return $h * 3600 + $m * 60 + $s;
        }
        // mm:ss
        if (count($parts) === 2) {
            $m = intval($parts[0]);
            $s = floatval($parts[1]);
            return $m * 60 + $s;
        }
    }
    return floatval($t);
}

$now_time = parse_time_to_seconds($now_raw);

// Ambiguous 3-part parsing fallback: treat "m:s:cs" (e.g. 1:31:00 -> 1m31.00s)
function parse_time_ambiguous_ms_cs($t) {
    if ($t === null || $t === '') return null;
    if (is_numeric($t)) return (float)$t;
    $t = trim($t);
    if (strpos($t, ':') === false) return parse_time_to_seconds($t);
    $parts = explode(':', $t);
    if (count($parts) === 3) {
        $m = intval($parts[0]);
        $s = intval($parts[1]);
        $cs = intval($parts[2]);
        return $m * 60 + $s + ($cs / 100.0);
    }
    return parse_time_to_seconds($t);
}

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
        $prev_raw = $prev['total_time'] ?? null;
        $prev_time = parse_time_to_seconds($prev_raw);
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
        $best_raw = $best['best_time'] ?? null;
        $best_time = parse_time_to_seconds($best_raw);
    }
}

// フォールバック: swim_best_tbl に値がなければ、履歴テーブルから最小値を取得して自己ベスト扱いにする
if ($best_time === null) {
    $sql_min = "SELECT MIN(total_time) AS min_time FROM swim_tbl WHERE group_id=? AND user_id=? AND pool=? AND event=? AND distance=?";
    $stmt_min = mysqli_prepare($link, $sql_min);
    if ($stmt_min) {
        $d_param = is_numeric($distance) ? (int)$distance : $distance;
        mysqli_stmt_bind_param($stmt_min, "ssssi", $group_id, $user_id, $pool, $event, $d_param);
        if (mysqli_stmt_execute($stmt_min)) {
            $rmin = mysqli_stmt_get_result($stmt_min);
            $rowmin = mysqli_fetch_assoc($rmin);
            $min_time = $rowmin['min_time'] ?? null;
            $best_time = parse_time_to_seconds($min_time);
        }
        mysqli_stmt_close($stmt_min);
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
            $raw = $row['total_time'];
            $row['raw_total_time'] = $raw;
            $row['total_time'] = parse_time_to_seconds($raw);
            $history[] = $row;
        }
    }
}

if (isset($stmt) && $stmt) mysqli_stmt_close($stmt);
mysqli_close($link);

// Post-normalization: if dataset median is short (<10min) but some parsed values are hours,
// reinterpret ambiguous 3-part raw strings as mm:ss:cs (common data-entry like "1:31:00" meant 1:31.00)
$numeric_times = array_filter(array_map(function($r){ return $r['total_time']; }, $history), function($v){ return $v !== null; });
$count = count($numeric_times);
$big_count = 0;
foreach ($numeric_times as $v) { if ($v > 3600) $big_count++; }
$median = null;
if ($count > 0) {
    sort($numeric_times);
    $mid = (int) floor($count/2);
    $median = $numeric_times[$mid];
}
if ($median !== null && $median < 600 && $big_count > 0) {
    foreach ($history as &$r) {
        if (isset($r['raw_total_time']) && substr_count($r['raw_total_time'], ':') === 2) {
            $r['total_time'] = parse_time_ambiguous_ms_cs($r['raw_total_time']);
        }
    }
    unset($r);
    if (isset($now_raw) && substr_count($now_raw, ':') === 2 && $now_time !== null && $now_time > 3600) {
        $now_time = parse_time_ambiguous_ms_cs($now_raw);
    }
    if (isset($prev_raw) && substr_count($prev_raw, ':') === 2 && $prev_time !== null && $prev_time > 3600) {
        $prev_time = parse_time_ambiguous_ms_cs($prev_raw);
    }
    if (isset($best_raw) && substr_count($best_raw, ':') === 2 && $best_time !== null && $best_time > 3600) {
        $best_time = parse_time_ambiguous_ms_cs($best_raw);
    }
}

/* 種目名変換 */
$event_map = [
    "fly" => "バタフライ",
    "ba"  => "背泳ぎ",
    "br"  => "平泳ぎ",
    "fr"  => "自由形",
    "im"  => "個人メドレー"
];

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../../HTML/swim_analysis.html.php';
