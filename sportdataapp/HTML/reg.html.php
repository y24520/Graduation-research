<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規登録</title>
    <link rel="stylesheet" href="../css/reg.css">
    <link rel="stylesheet" href="../css/site.css">
</head>

<body>

<h1>新規登録</h1>

<form action="" method="post">
<div class="reg-form">
    <div class="inner-reg-form">

        <!-- 左側 -->
        <div class="left-reg">
            <h3>ログイン情報</h3>

            <label for="group_id">団体ID</label><br>
            <input type="text" id="group_id" name="group_id" required><br>

            <label for="user_id">ユーザーID</label><br>
            <input type="text" id="user_id" name="user_id" required><br>

            <label for="password">パスワード</label><br>
            <input type="password" id="password" name="password" required><br>
        </div>

        <!-- 右側 -->
        <div class="right-reg">
            <h3>プロフィール情報</h3>

            <label for="name">氏名</label><br>
            <input type="text" id="name" name="name" required><br>

            <label for="dob">生年月日</label><br>
            <input type="date" id="dob" name="dob" required><br>

            <label for="height">身長</label><br>
            <input type="number" id="height" name="height" required><br>

            <label for="weight">体重</label><br>
            <input type="number" id="weight" name="weight" required><br>

            <label for="position">ポジション / 役職</label><br>
            <input type="text" id="position" name="position" required><br>
        </div>

    </div>

    <!-- 送信 -->
    <div class="sent-box">
        <input type="submit" name="reg" value="登録">
    </div>

</div>
</form>

</body>
</html>
