document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    // Проверяваме дали има съобщение за грешка в URL
    const urlParams = new URLSearchParams(window.location.search);
    const errorMessage = urlParams.get('error');
    if (errorMessage) {
        const errorElement = document.getElementById('loginError');
        errorElement.textContent = decodeURIComponent(errorMessage);
        errorElement.style.display = 'block';
    }

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        // Взимаме регистрираните потребители от localStorage
        const registeredUsers = JSON.parse(localStorage.getItem('registeredUsers')) || [];
        const user = registeredUsers.find(u => u.email === email);
        
        if (user && user.password === password) { // В реална ситуация паролите трябва да са хеширани
            // Успешен вход
            const userData = {
                email: user.email,
                firstName: user.firstName,
                lastName: user.lastName,
                isLoggedIn: true
            };
            
            localStorage.setItem('userData', JSON.stringify(userData));
            
            // Проверяваме дали има запазени данни от търсенето
            const savedBookingData = sessionStorage.getItem('bookingData');
            if (savedBookingData) {
                sessionStorage.removeItem('bookingData');
                window.location.href = 'search-results.html';
            } else {
                window.location.href = 'index.html';
            }
        } else {
            // Неуспешен вход - пренасочваме обратно с съобщение за грешка
            window.location.href = 'login.html?error=' + encodeURIComponent('Невалиден потребител или парола');
        }
    });
}); 