// Управление на резервациите
class BookingService {
    static async createBooking(bookingData) {
        try {
            const response = await fetch('api/bookings/create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(bookingData)
            });
            return await response.json();
        } catch (error) {
            console.error('Booking creation error:', error);
            return { message: 'Booking failed' };
        }
    }

    static async checkBooking(bookingRef, lastName) {
        try {
            const response = await fetch(`api/check_booking.php?booking_ref=${bookingRef}&last_name=${lastName}`);
            return await response.json();
        } catch (error) {
            console.error('Booking check error:', error);
            return { message: 'Booking check failed' };
        }
    }

    static async cancelBooking(bookingId) {
        try {
            const response = await fetch('api/cancel_booking.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ booking_id: bookingId })
            });
            return await response.json();
        } catch (error) {
            console.error('Booking cancellation error:', error);
            return { message: 'Cancellation failed' };
        }
    }
} 