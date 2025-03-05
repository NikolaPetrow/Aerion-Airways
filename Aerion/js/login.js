// document.addEventListener('DOMContentLoaded', function() {
//     const loginForm = document.getElementById('loginForm');
    
//     if (loginForm) {
//         loginForm.addEventListener('submit', function(e) {
//             e.preventDefault();
            
//             const username = document.getElementById('username').value;
//             const password = document.getElementById('password').value;
            
//             if (!username || !password) {
//                 const errorElement = document.getElementById('loginError');
//                 errorElement.textContent = 'Please fill in all fields';
//                 errorElement.style.display = 'block';
//                 return;
//             }
            
//             const formData = new FormData();
//             formData.append('username', username);
//             formData.append('password', password);
            
//             fetch('php/login.php', {
//                 method: 'POST',
//                 body: formData
//             })
//             .then(response => {
//                 if (!response.ok) {
//                     throw new Error('Network response was not ok');
//                 }
//                 return response.json();
//             })
//             .then(data => {
//                 if (data.status === 'success') {
//                     localStorage.setItem('userData', JSON.stringify(data.user));
//                     window.location.href = 'index.html';
//                 } else {
//                     const errorElement = document.getElementById('loginError');
//                     errorElement.textContent = data.message || 'Login failed';
//                     errorElement.style.display = 'block';
//                 }
//             })
//             .catch(error => {
//                 console.error('Error:', error);
//                 const errorElement = document.getElementById('loginError');
//                 errorElement.textContent = 'An error occurred during login';
//                 errorElement.style.display = 'block';
//             });
//         });
//     } else {
//         console.error('Login form not found');
//     }
// });