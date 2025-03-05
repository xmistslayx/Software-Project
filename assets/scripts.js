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

document.addEventListener('DOMContentLoaded', function() {
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