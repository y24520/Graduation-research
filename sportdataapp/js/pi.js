document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('piform');
    form.addEventListener('submit', (e) => {
        const height = form.height.value.trim();
        const weight = form.weight.value.trim();
        const sleeptime = form.sleep_time.value.trim();

        if (height === '' || weight === '' || sleeptime === ''){
            e.preventDefault();
            alert('必須項目を入力してください');
            return false;
        }

        if(height <= 0 || weight <= 0 ){
            e.preventDefault();
            alert('身長と体重の値が不正な入力です。');
            return false;
        }
    })
})