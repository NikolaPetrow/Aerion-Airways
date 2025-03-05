document.addEventListener('DOMContentLoaded', function() {
    // Проверка за логнат потребител
    const userData = JSON.parse(localStorage.getItem('userData'));
    if (!userData || !userData.isLoggedIn) {
        window.location.href = 'login.html';
        return;
    }

    // Зареждане на потребителските данни
    fetch('php/get_profile.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Not logged in');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                // Попълване на данните в профила
                document.getElementById('firstName').textContent = data.data.firstName;
                document.getElementById('lastName').textContent = data.data.lastName;
                document.getElementById('email').textContent = data.data.email;
                
                // Форматиране на датата
                const memberDate = new Date(data.data.memberSince);
                const formattedDate = memberDate.toLocaleDateString('bg-BG', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                document.getElementById('memberSince').textContent = formattedDate;
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Пренасочване към login при грешка
            window.location.href = 'login.html';
        });

    // Бутони за действия
    document.querySelector('.edit-button').addEventListener('click', function() {
        // TODO: Имплементация на редактиране на профила
    });

    document.querySelector('.change-password-button').addEventListener('click', function() {
        // TODO: Имплементация на промяна на паролата
    });
}); 