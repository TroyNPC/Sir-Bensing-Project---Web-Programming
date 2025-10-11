// assets/app.js
import './bootstrap.js';
import './styles/app.css';

// ✅ jQuery first
import $ from 'jquery';
window.$ = window.jQuery = $;

// ✅ Bootstrap (optional but usually needed)
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

// ✅ DataTables core + Bootstrap 5
import 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

// ✅ DataTables Responsive extension
import 'datatables.net-responsive-bs5';
import 'datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';


console.log('✅ DataTables responsive loaded');
