document.addEventListener('DOMContentLoaded', function () {
    // Helper selectors
    const villeSelect = document.getElementById('villeFilter');
    const table = document.querySelector('.card .table');

    // Filter rows by city name (client-side)
    function filterByCity(id) {
        if (!table) return;
        const rows = Array.from(table.tBodies[0].rows);
        if (!id) {
            rows.forEach(r => r.style.display = '');
            return;
        }
        // find city name from select option
        const opt = villeSelect.querySelector('option[value="' + id + '"]');
        const name = opt ? opt.textContent.trim() : null;
        if (!name) { rows.forEach(r => r.style.display = ''); return; }
        rows.forEach(r => {
            const villeCell = r.cells[1]; // second column is Ville
            const cellText = villeCell ? villeCell.textContent.trim() : '';
            if (cellText === name) r.style.display = ''; else r.style.display = 'none';
        });
    }

    if (villeSelect) {
        villeSelect.addEventListener('change', function () {
            filterByCity(this.value);
        });
    }
});
