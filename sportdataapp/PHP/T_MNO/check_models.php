<?php
// check_models.php
$api_key = 'AIzaSyBY5MSyB0vD3JaJuscbtSltGW85_i6_cK4'; // あなたのキーを入れる
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $api_key;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

echo "<h3>あなたのキーで使えるモデル一覧:</h3><ul>";
if (isset($data['models'])) {
    foreach ($data['models'] as $m) {
        // "models/gemini-1.5-flash" のような形式で表示される
        echo "<li>" . $m['name'] . " (サポート機能: " . implode(', ', $m['supportedGenerationMethods']) . ")</li>";
    }
} else {
    echo "エラー: モデルリストを取得できませんでした。キーを確認してください。";
    print_r($data);
}
echo "</ul>";
?>