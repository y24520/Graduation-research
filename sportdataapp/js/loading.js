$(function(){

  function end_loader() {
    $('.loader').fadeOut(800);
  }

  function show_load() {
    $('.loader .looping-rhombuses-spinner').fadeIn(400);
  }

  function hide_load() {
    $('.loader .looping-rhombuses-spinner').fadeOut(400);
  }

  function show_txt() {
    $('.loader .txt').fadeIn(400);
  }

  $(window).on('load', function () {
    setTimeout(show_load, 800);
    setTimeout(hide_load, 3500);
    setTimeout(show_txt, 4000);
    setTimeout(end_loader, 5000);
  });
});
