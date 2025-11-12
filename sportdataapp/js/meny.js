let menybtn = document.querySelector('.meny-btn');

let img = false;

menybtn.addEventListener('click', () => {
    img = !img;
    const nav = document.querySelector('.meny-nav');
    nav.classList.toggle('show');
    
    if(img){
        menybtn.style.backgroundImage = "url('../img/meny_open.png')";
    } else{
        menybtn.style.backgroundImage = "url('../img/meny_close.png')";
    }
})