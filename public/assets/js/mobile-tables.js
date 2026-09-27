/*
 * Pairs with assets/css/mobile-tables.css: on phones, tables wider than
 * their container become stacked cards. Each cell gets data-label from its
 * column heading. Re-checked on resize, tab/collapse changes and Livewire
 * updates. Tables in modals and tables with class "no-stack" are skipped.
 */
(function () {
    var PHONE_MAX = 767.98;

    function labelCells(table) {
        var headRow = table.tHead && table.tHead.rows[table.tHead.rows.length - 1];
        if (!headRow) return false;

        var labels = [];
        Array.prototype.forEach.call(headRow.cells, function (th) {
            var span = parseInt(th.getAttribute('colspan') || '1', 10);
            for (var s = 0; s < span; s++) labels.push(th.innerText.replace(/\s+/g, ' ').trim());
        });

        Array.prototype.forEach.call(table.tBodies, function (tbody) {
            Array.prototype.forEach.call(tbody.rows, function (tr) {
                var col = 0;
                Array.prototype.forEach.call(tr.cells, function (td) {
                    var span = parseInt(td.getAttribute('colspan') || '1', 10);
                    if (span === 1 && !td.hasAttribute('data-label')) {
                        td.setAttribute('data-label', labels[col] || '');
                    }
                    col += span;
                });
            });
        });

        return true;
    }

    function apply() {
        var phone = window.matchMedia('(max-width: ' + PHONE_MAX + 'px)').matches;

        document.querySelectorAll('table').forEach(function (table) {
            if (table.classList.contains('no-stack') || table.closest('.modal')) return;

            table.classList.remove('table-stacked');
            if (!phone || !table.tHead || !table.offsetParent) return;

            var box = table.parentElement;
            if (table.scrollWidth > box.clientWidth + 4 && labelCells(table)) {
                table.classList.add('table-stacked');
            }
        });
    }

    var timer;
    function schedule() {
        clearTimeout(timer);
        timer = setTimeout(apply, 120);
    }

    document.addEventListener('DOMContentLoaded', apply);
    window.addEventListener('load', apply);
    window.addEventListener('resize', schedule);
    document.addEventListener('shown.bs.tab', schedule);
    document.addEventListener('shown.bs.collapse', schedule);
    document.addEventListener('livewire:load', function () {
        if (window.Livewire && window.Livewire.hook) {
            window.Livewire.hook('message.processed', schedule);
        }
    });

    window.stackMobileTables = apply;
})();
