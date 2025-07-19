document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables
    const dataTableIds = ['#incomeTable', '#expenditureTable', '#clientTable', '#userTable'];
    dataTableIds.forEach(tableId => {
        const table = document.querySelector(tableId);
        if (table) {
            $(tableId).DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        }
    });

    // Add overlay div to body
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    // Toggle sidebar
    const toggleSidebar = () => {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
        document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
    };

    // Event listeners
    document.querySelector('.mobile-toggle')?.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    // Close sidebar on window resize if open
    window.addEventListener('resize', () => {
        if (window.innerWidth > 991) {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        }
    });

    // Handle table responsiveness
    const responsiveTables = document.querySelectorAll('.table');
    responsiveTables.forEach(table => {
        const wrapper = table.closest('.table-responsive');
        if (wrapper) {
            wrapper.addEventListener('scroll', function() {
                const isScrolling = this.scrollLeft > 0;
                this.classList.toggle('is-scrolling', isScrolling);
            });
        }
    });

    // Add swipe gesture support for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    document.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    }, false);

    document.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, false);

    const handleSwipe = () => {
        const sidebar = document.querySelector('.sidebar');
        const swipeLength = Math.abs(touchEndX - touchStartX);
        const threshold = 100; // minimum swipe distance

        if (swipeLength > threshold) {
            if (touchEndX > touchStartX) { // right swipe
                sidebar.classList.add('show');
                overlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            } else { // left swipe
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        }
    };
}); 