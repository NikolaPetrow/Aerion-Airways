document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const passwordToggle = document.getElementById('passwordToggle');
    const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');

    // Валидация в реално време
    const validatePassword = () => {
        const value = password.value;
        const requirements = {
            length: value.length >= 8,
            upper: /[A-Z]/.test(value),
            lower: /[a-z]/.test(value),
            number: /[0-9]/.test(value),
            special: /[!@#$%^&*]/.test(value)
        };

        // Актуализиране на индикаторите за изискванията
        document.getElementById('lengthCheck').classList.toggle('valid', requirements.length);
        document.getElementById('upperCheck').classList.toggle('valid', requirements.upper);
        document.getElementById('lowerCheck').classList.toggle('valid', requirements.lower);
        document.getElementById('numberCheck').classList.toggle('valid', requirements.number);
        document.getElementById('specialCheck').classList.toggle('valid', requirements.special);

        return Object.values(requirements).every(Boolean);
    };

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

    // Валидация при изпращане на формата
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        let isValid = true;
        const errors = {};

        // Валидация на имената
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        
        if (firstName.length < 2) {
            errors.firstName = 'First name must be at least 2 characters long';
            isValid = false;
        }
        
        if (lastName.length < 2) {
            errors.lastName = 'Last name must be at least 2 characters long';
            isValid = false;
        }

        // Валидация на имейл
        const email = document.getElementById('email').value.trim();
        if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            errors.email = 'Please enter a valid email address';
            isValid = false;
        }

        // Валидация на паролата
        if (!validatePassword()) {
            errors.password = 'Password does not meet requirements';
            isValid = false;
        }

        // Потвърждение на паролата
        if (password.value !== confirmPassword.value) {
            errors.confirmPassword = 'Passwords do not match';
            isValid = false;
        }

        // Проверка на съгласието с условията
        if (!document.querySelector('input[name="terms"]').checked) {
            errors.terms = 'You must agree to the Terms & Conditions';
            isValid = false;
        }

        // Показване на грешките
        Object.keys(errors).forEach(key => {
            const errorElement = document.getElementById(`${key}Error`);
            if (errorElement) {
                errorElement.textContent = errors[key];
            }
        });

        if (isValid) {
            // Взимаме съществуващите потребители или създаваме празен масив
            const registeredUsers = JSON.parse(localStorage.getItem('registeredUsers')) || [];
            
            // Проверяваме дали имейлът вече не е регистриран
            if (registeredUsers.some(user => user.email === email)) {
                errors.email = 'This email is already registered';
                document.getElementById('emailError').textContent = errors.email;
                return;
            }
            
            // Добавяме новия потребител
            const newUser = {
                firstName: firstName,
                lastName: lastName,
                email: email,
                password: password.value // В реална ситуация паролата трябва да е хеширана
            };
            
            registeredUsers.push(newUser);
            localStorage.setItem('registeredUsers', JSON.stringify(registeredUsers));
            
            // Автоматично влизаме с новия акаунт
            const userData = {
                firstName: firstName,
                lastName: lastName,
                email: email,
                isLoggedIn: true
            };
            localStorage.setItem('userData', JSON.stringify(userData));
            
            // Пренасочване към началната страница
            window.location.href = 'index.html';
        }
    });

    // Изчистване на грешките при промяна на полетата
    form.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', function() {
            const errorElement = document.getElementById(`${this.name}Error`);
            if (errorElement) {
                errorElement.textContent = '';
            }
            if (this.id === 'password') {
                validatePassword();
            }
        });
    });
}); 