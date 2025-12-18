<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>水泳｜分析</title>
<link rel="stylesheet" href="../../css/swim_analysis.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php $NAV_BASE = '..'; require_once __DIR__ . '/../PHP/header.php'; ?>

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
        <td><span id="best-now"></span> <span id="pb-badge" class="pb-badge" aria-live="polite"></span></td>
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
    </div>

    <div class="right-col">
        <!-- 推移グラフ -->
        <div class="chart-container">
            <canvas id="timeChart" height="px"></canvas>
        </div>

        <!-- 比較チャート（前回 vs 今回, ベスト vs 今回） -->
        <div class="compare-container">
            <div class="compare-chart">
                <h3>前回 vs 今回</h3>
                <canvas id="prevNowChart" ></canvas>
            </div>
            <div class="compare-chart">
                <h3>ベスト vs 今回</h3>
                <canvas id="bestNowChart"></canvas>
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
