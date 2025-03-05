document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Проверяваме дали потребителят е логнат (можете да добавите реална проверка по-късно)
    const isLoggedIn = false; // За демонстрация винаги е false
    
    if (!isLoggedIn) {
        // Запазваме данните от формата в sessionStorage
        const formData = {
            origin: document.getElementById('origin').value,
            destination: document.getElementById('destination').value,
            departure: document.getElementById('departure-date').value,
            return: document.getElementById('return-date').value
        };
        sessionStorage.setItem('bookingData', JSON.stringify(formData));
        
        // Пренасочваме към страницата за вход
        window.location.href = 'login.html';
    }
}); 