document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelector('.nav-links');
    const userData = JSON.parse(localStorage.getItem('userData'));

    function updateNavigation() {
        const loginBtn = navLinks.querySelector('.login-btn');
        
        if (userData && userData.isLoggedIn) {
            // Ако потребителят е логнат, заменяме бутона за вход с профил меню
            if (loginBtn) {
                const profileMenu = document.createElement('div');
                profileMenu.className = 'profile-menu';
                profileMenu.innerHTML = `
                    <button class="profile-btn">
                        <i class="fas fa-user-circle"></i>
                        <span>${userData.firstName}</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="profile-dropdown">
                        <a href="profile.html"><i class="fas fa-user"></i> My Profile</a>
                        <a href="bookings.html"><i class="fas fa-ticket-alt"></i> My Bookings</a>
                        <a href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                `;
                loginBtn.parentNode.replaceChild(profileMenu, loginBtn);

                // Добавяме функционалност за показване/скриване на dropdown менюто
                const profileBtn = profileMenu.querySelector('.profile-btn');
                const dropdown = profileMenu.querySelector('.profile-dropdown');
                
                profileBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('active');
                });

                // Затваряне на менюто при клик извън него
                document.addEventListener('click', () => {
                    dropdown.classList.remove('active');
                });

                // Функционалност за изход
                document.getElementById('logoutBtn').addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    fetch('php/logout.php')
                        .then(response => response.text())
                        .then(() => {
                            localStorage.removeItem('userData');
                            window.location.href = 'index.html';
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred during logout');
                        });
                });
            }
        }
    }

    updateNavigation();
}); 