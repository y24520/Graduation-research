$(function() {
  // showLoaderがtrueの場合のみローディングを表示
  if (typeof showLoader !== 'undefined' && showLoader) {
    // ランダムでラグの有無を決定（本番では条件分岐で制御可能）
    // true = ラグあり（遅い）、false = ラグなし（速い）
    const hasLag = Math.random() > 0.5;
    
    // 最初に loader を表示
    $('.loader').show();
    $('.spinner').show();
    $('.txt').hide();

    // ラグに応じてプログレスバーの速度を変更
    const progressBar = $('.loader .progress-bar');
    const loader = $('.loader');
    
    if (hasLag) {
      // ラグあり：遅くて不安定な動き
      console.log('ローディング: ラグあり（遅い）- オレンジ色');
      loader.addClass('lag');
      progressBar.css('animation', 'progressFillSlow 6s ease-in-out forwards');
      
      // 途中で一時停止する演出
      setTimeout(function() {
        progressBar.css('animation-play-state', 'paused');
      }, 2000);
      
      setTimeout(function() {
        progressBar.css('animation-play-state', 'running');
      }, 3000);
      
      // 5秒後にテキストに切り替え
      setTimeout(function(){
        $('.spinner').fadeOut(400, function() {
          $('.txt').fadeIn(400);
        });
      }, 5000);

      // さらに1.5秒後に loader を消す
      setTimeout(function(){
        $('.loader').fadeOut(800);
      }, 6500);
      
    } else {
      // ラグなし：高速で滑らか
      console.log('ローディング: ラグなし（速い）- ブルー色');
      progressBar.css('animation', 'progressFillFast 1.5s ease-out forwards');
      
      // 1.5秒後にテキストに切り替え
      setTimeout(function(){
        $('.spinner').fadeOut(400, function() {
          $('.txt').fadeIn(400);
        });
      }, 1500);

      // さらに0.8秒後に loader を消す
      setTimeout(function(){
        $('.loader').fadeOut(800);
      }, 2300);
    }
  }
});
