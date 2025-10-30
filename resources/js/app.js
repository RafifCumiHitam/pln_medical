/**
 * =====================================================
 * PLN Medical System — Main JS Bootstrap (SB Admin 2)
 * =====================================================
 * Konfigurasi ini sudah kompatibel dengan:
 * - Bootstrap 4.6.2
 * - jQuery 3.6.x
 * - Popper.js 1.16.x
 * - Select2 4.1.0-rc.0
 * =====================================================
 */

// Import bootstrap.js Laravel default (Axios, CSRF, dsb)
import './bootstrap';

// === jQuery global setup ===
import $ from 'jquery';
window.$ = window.jQuery = $;

// === Popper.js & Bootstrap 4 ===
import 'popper.js';
import 'bootstrap';

// === Select2 (plugin input select yang interaktif) ===
import 'select2';
import 'select2/dist/css/select2.min.css';

// === SB Admin 2 CSS/JS (dipanggil dari public via vite.config.js) ===
// File sb-admin-2.min.css dan js sudah di-handle via vite input

// === Auto-init komponen setelah DOM siap ===
$(document).ready(function () {
    // Select2 initializer
    if ($('.select2').length > 0) {
        $('.select2').select2({
            width: '100%',
            theme: 'bootstrap4'
        });
    }

    // Tooltip Bootstrap
    $('[data-toggle="tooltip"]').tooltip();

    // Scroll-to-top button smooth behavior
    $(document).on('click', 'a.scroll-to-top', function (e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });

    // Sidebar toggle handler (SB Admin 2)
    $('#sidebarToggle, #sidebarToggleTop').on('click', function () {
        $('body').toggleClass('sidebar-toggled');
        $('.sidebar').toggleClass('toggled');
        if ($('.sidebar').hasClass('toggled')) {
            $('.sidebar .collapse').collapse('hide');
        }
    });
});

console.log('✅ app.js loaded successfully — Bootstrap 4.6.2 & SB Admin 2 ready.');
