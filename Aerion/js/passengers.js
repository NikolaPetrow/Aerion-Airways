document.addEventListener('DOMContentLoaded', () => {
    const passengerBtn = document.querySelector('.passenger-btn');
    const passengerDropdown = document.querySelector('.passenger-dropdown');
    const countBtns = document.querySelectorAll('.count-btn');
    const passengerSummary = document.querySelector('.passenger-summary');

    // Показване/скриване на падащото меню
    passengerBtn.addEventListener('click', () => {
        passengerDropdown.classList.toggle('active');
    });

    // Затваряне при клик извън менюто
    document.addEventListener('click', (e) => {
        if (!passengerBtn.contains(e.target) && !passengerDropdown.contains(e.target)) {
            passengerDropdown.classList.remove('active');
        }
    });

    // Управление на броя пътници
    countBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.action;
            const countSpan = btn.parentElement.querySelector('span');
            let count = parseInt(countSpan.textContent);

            if (action === 'increase' && count < 9) {
                count++;
            } else if (action === 'decrease' && count > 0) {
                count--;
            }

            countSpan.textContent = count;
            btn.parentElement.querySelector('[data-action="decrease"]').disabled = count === 0;
            btn.parentElement.querySelector('[data-action="increase"]').disabled = count === 9;

            updatePassengerSummary();
        });
    });

    // Обновяване на обобщението
    function updatePassengerSummary() {
        const adults = parseInt(document.querySelector('.passenger-category:nth-child(1) .passenger-count span').textContent);
        const children = parseInt(document.querySelector('.passenger-category:nth-child(2) .passenger-count span').textContent);
        const infants = parseInt(document.querySelector('.passenger-category:nth-child(3) .passenger-count span').textContent);
        const cabinClass = document.querySelector('input[name="cabin"]:checked').value;

        const total = adults + children + infants;
        const passengerText = total === 1 ? 'Passenger' : 'Passengers';
        
        passengerSummary.textContent = `${total} ${passengerText}, ${cabinClass.charAt(0).toUpperCase() + cabinClass.slice(1)}`;
    }

    // Обновяване при промяна на класата
    document.querySelectorAll('input[name="cabin"]').forEach(radio => {
        radio.addEventListener('change', updatePassengerSummary);
    });
}); 