<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>水泳｜記録</title>
    <link rel="stylesheet" href="../../css/swim_input.css">
    <link rel="stylesheet" href="../../css/site.css">
    
</head>
<body>
<?php $NAV_BASE = '..'; require_once __DIR__ . '/../PHP/header.php'; ?>

<div class="container">
    <div class="chart-container" style="display: flex; justify-content: center;padding:18px; margin:0 auto;">
        <?php if ($showSuccess): ?>
            <p class="success">記録を保存しました</p>
        <?php endif; ?>

        <form method="post" id="swim-form">

  <!-- タイム（中央・横長） -->
   <label>タイム</label>
  <input type="text" id="time" readonly>
  <input type="hidden" id="total_time" name="total_time">

  <!-- 3列エリア -->
  <div class="form-items">

    <!-- 左：基本情報 -->
    <div class="form-basic">
      <label>日付</label>
      <input type="date" name="swim_date" required>

      <label>大会名</label>
      <input type="text" name="meet_name">

      <label>ラウンド</label>
      <select name="round">
        <option value="予選">予選</option>
        <option value="準決勝">準決勝</option>
        <option value="決勝">決勝</option>
        <option value="タイム決勝">タイム決勝</option>
      </select>

      <label>体調</label>
      <select name="condition">
        <option value="5">とても良い</option>
        <option value="4">良い</option>
        <option value="3">普通</option>
        <option value="2">悪い</option>
        <option value="1">とても悪い</option>
      </select>

      <label>プール</label>
      <select id="pool_type" name="pool" required>
        <option value="" selected disabled>選択してください</option>
        <option value="short">短水路</option>
        <option value="long">長水路</option>
      </select>

      <label>種目</label>
      <select id="event" name="event" required>
        <option value="" selected disabled>選択してください</option>
        <option value="fly">バタフライ</option>
        <option value="ba">背泳ぎ</option>
        <option value="br">平泳ぎ</option>
        <option value="fr">自由形</option>
        <option value="im">個人メドレー</option>
      </select>

      <label>距離</label>
      <select id="distance" name="distance" required>
        <option value="" selected disabled>選択してください</option>
        <option value="25">25m</option>
        <option value="50">50m</option>
        <option value="100">100m</option>
        <option value="200">200m</option>
        <option value="400">400m</option>
        <option value="800">800m</option>
        <option value="1500">1500m</option>
      </select>
    </div>

    <!-- 中央：ストローク -->
    <div id="stroke_area" class="form-stroke">
      <label>ストローク回数</label>
    </div>

    <!-- 右：ラップ -->
    <div id="lap_time_area" class="form-lap">
      <label>ラップタイム</label>
    </div>

    <!-- 保存ボタン -->
    <div class="form-submit">
      <input type="submit" value="保存">
    </div>

  </div>
</form>

    </div>
</div>

<script src="../../js/swim/swim_input.js"></script>
</body>
</html>
