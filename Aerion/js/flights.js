// Управление на полетите
class FlightService {
    static async searchFlights(from, to, date) {
        try {
            const response = await fetch(`api/flights/search.php?from=${from}&to=${to}&date=${date}`);
            return await response.json();
        } catch (error) {
            console.error('Flight search error:', error);
            return [];
        }
    }

    static async searchAirports(query) {
        try {
            const response = await fetch(`api/airports/search.php?q=${query}`);
            return await response.json();
        } catch (error) {
            console.error('Airport search error:', error);
            return [];
        }
    }
}

// Актуализиране на формата за търсене
document.addEventListener('DOMContentLoaded', function() {
    const originInput = document.getElementById('origin');
    const destinationInput = document.getElementById('destination');

    // Автокомплийт за летища
    async function setupAirportAutocomplete(input) {
        input.addEventListener('input', async function() {
            const results = await FlightService.searchAirports(this.value);
            // Показване на резултатите в dropdown
            updateAirportDropdown(results, input);
        });
    }

    setupAirportAutocomplete(originInput);
    setupAirportAutocomplete(destinationInput);
}); 