<!DOCTYPE html>
<html lang="jp">
    <head>
        <meta charset="UTF-8">
        <title>スポーツデータ分析可視化アプリ</title>
        <link rel="stylesheet" href="../css/login.css">
    </head>
    <body>
    <div class=all>
        <h1>LOGIN</h1>
        <div class="login-form">
            <!-- 左側 -->
            <div class="left-login-form">
                <h2>ログイン</h2>
                <form action="" method="post" id="login-form">
                    <!-- 団体ID -->
                    <label for="group-id">ID</label><br>
                    <input type="text" id="group_id" name="group_id"  placeholder="団体IDを入力してください" required><br> 
                    <!-- ユーザーID -->
                    <label for="user-id">ユーザーID</label><br> 
                    <input type="text" id="user_id" name="user_id"  placeholder="ユーザーIDを入力してください" required><br>

                    <!-- パスワード -->
                    <label for="password">パスワード</label><br> 
                    <input type="password" id="password" name="password"  placeholder="パスワードIDを入力してください" required><br>

                    <!-- ログイン -->
                    <div class="send-box">
                       <input type="submit" name="send" value="ログイン">
                    </div>
                </form>
            </div>
            <!-- 右側 -->
            <div class="right-reg">
                <h2>はじめてご利用の方</h2>
                <p>ご利用には新規登録が必要です</p>
                <a href="reg.php">新規登録はこちら</a>
            </div>
        </div>
        </div>
    </body>
</html>
