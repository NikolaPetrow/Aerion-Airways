document.addEventListener('DOMContentLoaded', function() {
    // Проверка за логнат потребител
    const isLoggedIn = checkLoginStatus(); // Функция от auth-status.js
    const hasBooking = false; // Това ще идва от базата данни

    const loginPrompt = document.getElementById('loginPrompt');
    const noBooking = document.getElementById('noBooking');
    const flightStatus = document.getElementById('flightStatus');

    if (!isLoggedIn) {
        // Показваме съобщение за вход
        loginPrompt.style.display = 'block';
        noBooking.style.display = 'none';
        flightStatus.style.display = 'none';
    } else if (!hasBooking) {
        // Показваме съобщение за липса на резервация
        loginPrompt.style.display = 'none';
        noBooking.style.display = 'block';
        flightStatus.style.display = 'none';
    } else {
        // Показваме информация за полета
        loginPrompt.style.display = 'none';
        noBooking.style.display = 'none';
        flightStatus.style.display = 'block';
        
        // Тук ще се добави логика за зареждане на информацията за полета от базата данни
    }
}); 