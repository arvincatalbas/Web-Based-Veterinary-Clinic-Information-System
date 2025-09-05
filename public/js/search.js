document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('petSearchInput');
    const clearSearchBtn = document.getElementById('clearSearch');
    const tableBody = document.getElementById('petTableBody');
    const originalRows = Array.from(tableBody.querySelectorAll('tr'));

    // Store original content for highlighting
    const originalContent = originalRows.map(row => {
        const petNameCell = row.querySelector('td:nth-child(2)'); // Pet name is in the 2nd column
        return petNameCell ? petNameCell.textContent.trim() : '';
    });

    // Search function
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();

        // Show/hide clear button
        if (searchTerm.length > 0) {
            clearSearchBtn.style.display = 'flex';
        } else {
            clearSearchBtn.style.display = 'none';
        }

        // Filter rows
        originalRows.forEach((row, index) => {
            const petName = originalContent[index];

            if (searchTerm === '' || petName.toLowerCase().includes(searchTerm)) {
                row.style.display = '';

                // Highlight matching text if search term exists
                if (searchTerm !== '') {
                    highlightText(row, searchTerm);
                } else {
                    removeHighlight(row);
                }
            } else {
                row.style.display = 'none';
            }
        });

        // Show no results message if no matches
        showNoResultsMessage(searchTerm);
    }

    // Highlight matching text
    function highlightText(row, searchTerm) {
        const petNameCell = row.querySelector('td:nth-child(2)');
        if (petNameCell) {
            const originalText = originalContent[originalRows.indexOf(row)];
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            const highlightedText = originalText.replace(regex, '<span class="highlight">$1</span>');
            petNameCell.innerHTML = highlightedText;
        }
    }

    // Remove highlighting
    function removeHighlight(row) {
        const petNameCell = row.querySelector('td:nth-child(2)');
        if (petNameCell) {
            const originalText = originalContent[originalRows.indexOf(row)];
            petNameCell.textContent = originalText;
        }
    }

    // Show no results message
    function showNoResultsMessage(searchTerm) {
        const visibleRows = originalRows.filter(row => row.style.display !== 'none');

        // Remove existing no results message
        const existingNoResults = tableBody.querySelector('.no-results-row');
        if (existingNoResults) {
            existingNoResults.remove();
        }

        // Add no results message if no visible rows and search term exists
        if (visibleRows.length === 0 && searchTerm !== '') {
            const noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-row';
            noResultsRow.innerHTML = `
                <td colspan="10" class="no-results">
                    <i class="fas fa-search"></i>
                    <div>No pets found matching "${searchTerm}"</div>
                    <small>Try a different search term</small>
                </td>
            `;
            tableBody.appendChild(noResultsRow);
        }
    }

    // Event listeners
    searchInput.addEventListener('input', performSearch);
    searchInput.addEventListener('keyup', function (e) {
        if (e.key === 'Escape') {
            clearSearch();
        }
    });

    clearSearchBtn.addEventListener('click', clearSearch);

    // Clear search function
    function clearSearch() {
        searchInput.value = '';
        clearSearchBtn.style.display = 'none';

        // Show all rows and remove highlighting
        originalRows.forEach(row => {
            row.style.display = '';
            removeHighlight(row);
        });

        // Remove no results message
        const existingNoResults = tableBody.querySelector('.no-results-row');
        if (existingNoResults) {
            existingNoResults.remove();
        }

        // Focus back to search input
        searchInput.focus();
    }

    // Add some nice animations
    searchInput.addEventListener('focus', function () {
        this.parentElement.style.transform = 'scale(1.02)';
    });

    searchInput.addEventListener('blur', function () {
        this.parentElement.style.transform = 'scale(1)';
    });

    // Add loading state for better UX
    let searchTimeout;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 150); // Debounce search
    });
});