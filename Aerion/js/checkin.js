document.addEventListener('DOMContentLoaded', function() {
    const steps = document.querySelectorAll('.checkin-section');
    const stepIndicators = document.querySelectorAll('.step');
    let currentStep = 1;

    // Form submission handler
    const form = document.getElementById('retrieveBookingForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const bookingRef = document.getElementById('bookingReference').value;
        const lastName = document.getElementById('lastName').value;
        
        // Тук ще се добави функционалност за проверка в базата данни
        alert('Check-in functionality will be implemented when the database is ready.');
    });

    // Initialize seat map
    initializeSeatMap();

    window.nextStep = function() {
        if (currentStep < 4) {
            document.querySelector(`#step${currentStep}`).classList.remove('active');
            document.querySelector(`#step${currentStep + 1}`).classList.add('active');
            
            stepIndicators[currentStep - 1].classList.remove('active');
            stepIndicators[currentStep].classList.add('active');
            
            currentStep++;
        }
    };

    window.previousStep = function() {
        if (currentStep > 1) {
            document.querySelector(`#step${currentStep}`).classList.remove('active');
            document.querySelector(`#step${currentStep - 1}`).classList.add('active');
            
            stepIndicators[currentStep - 1].classList.remove('active');
            stepIndicators[currentStep - 2].classList.add('active');
            
            currentStep--;
        }
    };

    window.downloadBoardingPass = function() {
        // Implementation for downloading boarding pass
        alert('Boarding pass download will be implemented');
    };

    window.printBoardingPass = function() {
        window.print();
    };

    // Форматиране на booking reference в главни букви
    const bookingInput = document.getElementById('bookingReference');
    bookingInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});

function initializeSeatMap() {
    const seatMap = document.getElementById('seatMap');
    const rows = 10;
    const seatsPerRow = 6;
    
    for (let i = 1; i <= rows; i++) {
        const row = document.createElement('div');
        row.className = 'seat-row';
        row.style.display = 'flex';
        row.style.justifyContent = 'center';
        row.style.gap = '10px';
        row.style.marginBottom = '10px';
        
        for (let j = 0; j < seatsPerRow; j++) {
            const seat = document.createElement('div');
            seat.className = 'seat available';
            seat.style.cursor = 'pointer';
            
            // Add seat number
            const seatLetter = String.fromCharCode(65 + j);
            seat.setAttribute('data-seat', `${i}${seatLetter}`);
            seat.innerHTML = `${i}${seatLetter}`;
            
            // Add click handler
            seat.addEventListener('click', function() {
                if (!this.classList.contains('occupied')) {
                    document.querySelectorAll('.seat.selected').forEach(s => {
                        s.classList.remove('selected');
                        s.classList.add('available');
                    });
                    this.classList.remove('available');
                    this.classList.add('selected');
                    document.getElementById('seatNumber').textContent = this.getAttribute('data-seat');
                }
            });
            
            // Randomly mark some seats as occupied
            if (Math.random() < 0.3) {
                seat.classList.remove('available');
                seat.classList.add('occupied');
                seat.style.cursor = 'not-allowed';
            }
            
            row.appendChild(seat);
            
            // Add aisle
            if (j === 2) {
                const aisle = document.createElement('div');
                aisle.style.width = '20px';
                row.appendChild(aisle);
            }
        }
        
        seatMap.appendChild(row);
    }
} 