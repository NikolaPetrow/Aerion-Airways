// Проверка на автентикацията при зареждане на всяка страница
function checkAuth() {
    fetch('php/check-auth.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.isLoggedIn) {
                // Потребителят е логнат
                updateNavigation(true, data.user.name);
            } else {
                // Потребителят не е логнат
                updateNavigation(false);
            }
        })
        .catch(error => {
            console.error('Auth check failed:', error);
            updateNavigation(false);
        });
}

// Актуализиране на навигацията според статуса на логване
function updateNavigation(isLoggedIn, userName = '') {
    const loginBtn = document.querySelector('.login-btn');
    if (isLoggedIn) {
        // Показване на потребителското меню
        loginBtn.innerHTML = `
            <div class="user-menu">
                <span>${userName}</span>
                <a href="profile.html">My Profile</a>
                <a href="#" onclick="logout()">Logout</a>
            </div>
        `;
    } else {
        // Показване на бутона за вход
        loginBtn.innerHTML = '<a href="login.html">Login</a>';
    }
}

// Функция за изход
function logout() {
    fetch('php/logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = 'index.html';
            }
        })
        .catch(error => console.error('Logout failed:', error));
}

// Изпълняване на проверката при зареждане на страницата
document.addEventListener('DOMContentLoaded', checkAuth); 