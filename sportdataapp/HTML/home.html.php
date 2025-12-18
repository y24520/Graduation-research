<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ホームページ</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/site.css">

    <script>
        const eventsFromPHP = <?= json_encode($records, JSON_UNESCAPED_UNICODE); ?>;
        const showLoader = <?= $showLoader ? 'true' : 'false' ?>;
    </script>
</head>
<body>
<?php if ($showLoader): ?>
    <div class="loader">
        <div class="spinner"></div>
        <p class="txt">こんにちは！<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>さん</p>
    </div>
<?php endif; ?>

<?php $NAV_BASE = '.'; require_once __DIR__ . '/../PHP/header.php'; ?>

<div class="home">
    <!-- ホーム画面 -->
    <div class="home-all">
        <!-- 左側 -->
        <div class="home-left">
            <!--　ユーザー情報 -->
            <div class="user">
                <h2>ユーザー情報</h2>
                <div class="user-border">
                    <div class="photo">
                        <img src="../img/default-avatar.png" width="270px" height="300px">
                    </div>
                    <table class="user-information">
                        <tr><th>氏名</th><td><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></td></tr>
                        <tr><th>生年月日</th><td><?= htmlspecialchars($userDob, ENT_QUOTES, 'UTF-8') ?></td></tr>
                        <tr><th>身長</th><td><?= htmlspecialchars($userHeight, ENT_QUOTES, 'UTF-8') ?> cm</td></tr>
                        <tr><th>体重</th><td><?= htmlspecialchars($userWeight, ENT_QUOTES, 'UTF-8') ?> kg</td></tr>
                        <tr><th>ポジション</th><td><?= htmlspecialchars($userPosition, ENT_QUOTES, 'UTF-8') ?></td></tr>
                    </table>
                </div>
            </div>

            <!-- メッセージ -->
            <div class="messege">
                <h2>お知らせ</h2>
                <div class="messege-area"></div>
            </div>
        </div>

        <!-- 右側 -->
        <div class="home-right">
            <!-- 目標 -->
            <div class="goal">
                <h2>目標</h2>
                <div class="goal-border">
                    <!-- 今月の目標が未登録の場合：入力フォーム表示 -->
                    <form id="goal-form" <?= $hasGoalThisMonth ? 'class="hidden"' : '' ?> action="goalsave.php" method="post">
                        <input type="text" id="goal" name="goal" placeholder="今月の目標を入力" value="<?= htmlspecialchars($corrent_goal, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="submit" id="goal-reg" name="submit" value="登録">
                    </form>
                    
                    <!-- 今月の目標が登録済みの場合:現在の目標表示 -->
                    <div id="goal-display" class="<?= !$hasGoalThisMonth ? 'hidden' : '' ?>">
                        <p class="now-goal"><?= htmlspecialchars($corrent_goal ?: '目標が登録されていません', ENT_QUOTES, 'UTF-8') ?></p>
                        <button type="button" id="edit-goal-btn">変更</button>
                    </div>
                </div>
            </div>
            <div class="calendar">
                <h2>カレンダー</h2>
                <div id="calendar-area" class="calendar-area"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
if (showLoader) {
    $.getScript("../js/loading.js");
}
</script>

<script src="../js/fullcalendar/dist/index.global.min.js"></script>
<script src="../js/fullcalendar/packages/interaction/index.global.min.js"></script>
<script src="../js/fullcalendar/packages/daygrid/index.global.min.js"></script>
<script src="../js/calendar.js"></script>

<script>
// 目標の変更ボタン処理
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('edit-goal-btn');
    const goalForm = document.getElementById('goal-form');
    const goalDisplay = document.getElementById('goal-display');
    
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            goalDisplay.classList.add('hidden');
            goalForm.classList.remove('hidden');
            document.getElementById('goal').focus();
        });
    }
});
</script>

</body>
</html>
