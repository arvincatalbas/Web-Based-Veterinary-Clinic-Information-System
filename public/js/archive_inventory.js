// Toggle select all functionality
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            
    itemCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
            
    updateDeleteButton();
}
        
// Select all items
function selectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    selectAllCheckbox.checked = true;
    toggleSelectAll();
}
        
// Update delete button state
function updateDeleteButton() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            
    // Enable/disable delete button based on checked items
    deleteBtn.disabled = checkedBoxes.length === 0;
            
    // Update select all checkbox state
    const totalCheckboxes = document.querySelectorAll('.item-checkbox');
    selectAllCheckbox.checked = checkedBoxes.length === totalCheckboxes.length && totalCheckboxes.length > 0;
    selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < totalCheckboxes.length;
}
        
// Delete selected items
function deleteSelected() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
            
    if (checkedBoxes.length === 0) {
        alert('Please select items to delete.');
        return;
    }
            
    const confirmMessage = `Are you sure you want to permanently delete ${checkedBoxes.length} item(s)? This action cannot be undone.`;
            
    if (confirm(confirmMessage)) {
        // Create a form to submit selected items
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'archive_inventory.php';
                
        checkedBoxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_items[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });
                
        const deleteInput = document.createElement('input');
        deleteInput.type = 'hidden';
        deleteInput.name = 'delete_selected';
        deleteInput.value = '1';
        form.appendChild(deleteInput);
                
        document.body.appendChild(form);
        form.submit();
    }
}
        
// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateDeleteButton();
});