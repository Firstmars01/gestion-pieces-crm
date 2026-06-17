document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchStepsInput');
    const tableBody = document.getElementById('stepsTableBody');

    if (searchInput && tableBody) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = tableBody.getElementsByTagName('tr');

            for (let row of rows) {
                if (row.cells.length === 1) continue;

                const rowText = row.textContent.toLowerCase();

                if (rowText.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }
});
