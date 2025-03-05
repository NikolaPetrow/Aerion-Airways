document.addEventListener('DOMContentLoaded', function() {
    const loginButton = document.querySelector('a[href="login.html"]');
    if (loginButton) {
        loginButton.addEventListener('click', function(e) {
            sessionStorage.setItem('previousPage', window.location.href);
        });
    }
}); 