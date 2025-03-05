document.addEventListener('DOMContentLoaded', function() {
    // Trip type buttons
    const tripButtons = document.querySelectorAll('.trip-type-btn');
    const returnDateCol = document.querySelector('.dates .search-col:last-child');

    tripButtons.forEach(button => {
        button.addEventListener('click', function() {
            tripButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            if (this.dataset.type === 'one-way') {
                returnDateCol.style.display = 'none';
            } else {
                returnDateCol.style.display = 'block';
            }
        });
    });

    // Swap button
    const swapButton = document.querySelector('.swap-btn');
    const originInput = document.getElementById('origin');
    const destinationInput = document.getElementById('destination');

    swapButton.addEventListener('click', function() {
        const temp = originInput.value;
        originInput.value = destinationInput.value;
        destinationInput.value = temp;
    });

    // Passenger count buttons
    const countButtons = document.querySelectorAll('.count-btn');
    countButtons.forEach(button => {
        button.addEventListener('click', function() {
            const countSpan = this.parentElement.querySelector('span');
            let count = parseInt(countSpan.textContent);
            
            if (this.dataset.action === 'increase') {
                count++;
            } else {
                count--;
            }
            
            countSpan.textContent = count;
            button.parentElement.querySelector('[data-action="decrease"]').disabled = count <= 0;
            updatePassengerSummary();
        });
    });

    // Passenger dropdown
    const passengerBtn = document.querySelector('.passenger-btn');
    const passengerDropdown = document.querySelector('.passenger-dropdown');

    passengerBtn.addEventListener('click', function() {
        passengerDropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.passenger-select')) {
            passengerDropdown.classList.remove('show');
        }
    });

    // Cabin class selection
    const cabinOptions = document.querySelectorAll('input[name="cabin"]');
    cabinOptions.forEach(option => {
        option.addEventListener('change', updatePassengerSummary);
    });

    // Search form submission
    const searchForm = document.getElementById('bookingForm');
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Add your search logic here
        alert('Search functionality will be implemented soon!');
    });

    function updatePassengerSummary() {
        const adults = parseInt(document.querySelector('.passenger-category:nth-child(1) .passenger-count span').textContent);
        const children = parseInt(document.querySelector('.passenger-category:nth-child(2) .passenger-count span').textContent);
        const infants = parseInt(document.querySelector('.passenger-category:nth-child(3) .passenger-count span').textContent);
        const selectedCabin = document.querySelector('input[name="cabin"]:checked').value;
        
        const total = adults + children + infants;
        const passengerText = total === 1 ? 'Passenger' : 'Passengers';
        
        document.querySelector('.passenger-summary').textContent = 
            `${total} ${passengerText}, ${selectedCabin.charAt(0).toUpperCase() + selectedCabin.slice(1)}`;
    }
}); 