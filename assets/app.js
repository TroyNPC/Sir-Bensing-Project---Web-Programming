import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import 'bootstrap/dist/css/bootstrap.min.css';

// start the Stimulus application
import './bootstrap';

// enable the interactive UI components from Flowbite
import 'flowbite';
console.log('yes')

const links = [document.getElementById('homebutton'),
document.getElementById('aboutbutton'),
document.getElementById('homebutton'),
document.getElementById('aboutbutton'),
document.getElementById('contact')
];

  document.addEventListener("DOMContentLoaded", function () {
    const navbarCollapse = document.getElementById("navbarContent");
    const navLinks = document.querySelectorAll(".nav-link, .btn, .form-control");

    navLinks.forEach(link => {
      link.addEventListener("click", () => {
        if (navbarCollapse.classList.contains("show")) {
          new bootstrap.Collapse(navbarCollapse).hide();
        }
      });
    });
  });

