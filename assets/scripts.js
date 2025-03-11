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


// payments_page js
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