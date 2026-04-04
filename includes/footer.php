<?php
/**
 * Footer Component
 * LogicERP Modular Framework
 */
?>

    </div> <!-- End Main Content (opened in header.php) -->
    
    <script>
        // Common Tooltips & Popovers
        $(function () {
            // Placeholder for common interactivity
            console.log("LogicERP Framework Initialized");
        });
    </script>
    <script>
    $(function() {
        // 1. Restore Sidebar State
        if (localStorage.getItem('sidebarState') === 'collapsed') {
            $('body').addClass('sidebar-collapsed');
        }

        // 2. Sidebar Toggle Logic
        $('#sidebarToggle').on('click', function() {
            $('body').toggleClass('sidebar-collapsed');
            const state = $('body').hasClass('sidebar-collapsed') ? 'collapsed' : 'expanded';
            localStorage.setItem('sidebarState', state);
        });
    });
    </script>
</body>
</html>
