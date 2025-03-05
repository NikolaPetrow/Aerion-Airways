document.addEventListener('DOMContentLoaded', function() {
    // Проверяваме дали има логнат потребител
    const userData = JSON.parse(localStorage.getItem('userData'));
    if (userData && userData.isLoggedIn) {
        // Показваме приветствието
        const userWelcome = document.getElementById('userWelcome');
        const userName = document.getElementById('userName');
        if (userWelcome && userName) {
            userName.textContent = userData.firstName;
            userWelcome.style.display = 'block';
        }

        // Актуализираме навигацията
        const loginBtn = document.querySelector('.login-btn');
        if (loginBtn) {
            loginBtn.innerHTML = `
                <div class="user-menu">
                    <span>${userData.firstName} ${userData.lastName}</span>
                    <a href="profile.html">My Profile</a>
                    <a href="#" onclick="logout()">Logout</a>
                </div>
            `;
        }
    }
});

function logout() {
    fetch('php/logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                localStorage.removeItem('userData');
                window.location.reload();
            }
        })
        .catch(error => console.error('Logout failed:', error));
} 