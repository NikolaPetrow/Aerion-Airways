document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const passwordToggle = document.getElementById('passwordToggle');
    const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');

    // Показване/скриване на паролата
    const togglePassword = (inputId, toggleId) => {
        const input = document.getElementById(inputId);
        const toggle = document.getElementById(toggleId);
        
        if (input.type === 'password') {
            input.type = 'text';
            toggle.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            toggle.classList.replace('fa-eye-slash', 'fa-eye');
        }
    };

    passwordToggle.addEventListener('click', () => togglePassword('password', 'passwordToggle'));
    confirmPasswordToggle.addEventListener('click', () => togglePassword('confirmPassword', 'confirmPasswordToggle'));

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (document.getElementById('password').value !== document.getElementById('confirmPassword').value) {
            return;
        }

        const formData = new FormData();
        formData.append('firstName', document.getElementById('firstName').value.trim());
        formData.append('lastName', document.getElementById('lastName').value.trim());
        formData.append('email', document.getElementById('email').value.trim());
        formData.append('password', document.getElementById('password').value);

        fetch('php/register.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes('successful')) {
                window.location.href = 'login.html';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
}); 