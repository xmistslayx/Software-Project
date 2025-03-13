function toggleAddForm() {
    const addForm = document.getElementById('add-form');
    if (addForm.style.display === 'none' || addForm.style.display === '') {
        addForm.style.display = 'block';
    } else {
        addForm.style.display = 'none';
    }
}

function toggleEditForm(id) {
    const editForm = document.getElementById(`edit-form-${id}`);
    if (editForm.style.display === 'none' || editForm.style.display === '') {
        editForm.style.display = 'block';
    } else {
        editForm.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    hideAllEditForms();
    hideAllAddForms();
});

function hideAllEditForms() {
    const editForms = document.querySelectorAll('.edit-form');
    editForms.forEach(form => {
        form.style.display = 'none';
    });
}

function hideAllAddForms() {
    const addForms = document.querySelectorAll('.add-form');
    addForms.forEach(form => {
        form.style.display = 'none';
    });
}

// manage_bookings
document.addEventListener("DOMContentLoaded", function () {
    const bookedDates = JSON.parse(document.getElementById("booked-dates").textContent);

    const allDateInputs = document.querySelectorAll('input[type="date"]');

    allDateInputs.forEach(function (dateInput) {
        flatpickr(dateInput, {
            minDate: "today",
            dateFormat: "Y-m-d",
            onDayCreate: function (dObj, dStr, fp, dayElem) {
                const currentDate = dayElem.dateObj;

                let isBooked = bookedDates.some(booking => {
                    const startDate = new Date(booking.start);
                    const endDate = new Date(booking.end);

                    startDate.setHours(0, 0, 0, 0);
                    endDate.setHours(0, 0, 0, 0);
                    currentDate.setHours(0, 0, 0, 0);

                    return currentDate >= startDate && currentDate <= endDate;
                });

                if (isBooked) {
                    dayElem.classList.add("booked-date");
                    dayElem.style.backgroundColor = "#ffcccc";
                    dayElem.style.color = "#666";
                } else {
                    dayElem.style.backgroundColor = "#ffffff";
                }
            }
        });
    });
});

// payments_page
function updateBookingDetails() {
    const bookingSelect = document.getElementById('booking_selection');
    const selectedOption = bookingSelect.options[bookingSelect.selectedIndex];

    if (selectedOption.value) {
        const roomId = selectedOption.getAttribute('data-room-id');
        const checkInDate = selectedOption.getAttribute('data-check-in');
        const checkOutDate = selectedOption.getAttribute('data-check-out');

        document.getElementById('room_id').value = roomId;
        document.getElementById('check_in_date').value = checkInDate;
        document.getElementById('check_out_date').value = checkOutDate;

        calculateAmount();
    } else {
        document.getElementById('room_id').value = '';
        document.getElementById('check_in_date').value = '';
        document.getElementById('check_out_date').value = '';
        document.getElementById('amount').value = '';
    }
}

function calculateAmount() {
    const paymentType = document.getElementById('payment_type').value;
    document.getElementById("payment_type_hidden").value = paymentType;

    const checkInDate = document.getElementById('check_in_date').value;
    const checkOutDate = document.getElementById('check_out_date').value;
    const roomId = document.getElementById('room_id').value;
    let amount = 0;

    if (checkInDate && checkOutDate && roomId && roomRates[roomId]) {
        switch (paymentType) {
            case 'rent':
                const roomRate = roomRates[roomId] || 0;
                amount = roomRate.toFixed(2);
                console.log(amount);
                break;
            case 'food':
                amount = 50;
                break;
            case 'laundry':
                amount = 20;
                break;
        }
    }

    document.getElementById('amount').value = amount;
}


window.onload = function () {

    const bookingSelect = document.getElementById('booking_selection');
    if (bookingSelect.options.length > 1) {
        bookingSelect.selectedIndex = 1;
        updateBookingDetails();
    }
};