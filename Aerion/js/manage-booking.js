document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchBookingForm');
    const bookingDetails = document.getElementById('bookingDetails');

    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const bookingRef = document.getElementById('bookingReference').value;
        const lastName = document.getElementById('lastName').value;

        // За демонстрация показваме детайлите
        bookingDetails.style.display = 'block';
        searchForm.parentElement.style.display = 'none';
    });

    // Бутони за действия
    document.querySelector('.action-button.modify').addEventListener('click', function() {
        alert('Modify booking functionality will be implemented');
    });

    document.querySelector('.action-button.cancel').addEventListener('click', function() {
        if (confirm('Are you sure you want to cancel this booking?')) {
            alert('Cancel booking functionality will be implemented');
        }
    });

    document.querySelector('.action-button.print').addEventListener('click', function() {
        window.print();
    });

    // Форматиране на booking reference в главни букви
    const bookingInput = document.getElementById('bookingReference');
    bookingInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
}); 