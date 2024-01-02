/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (showcase.css in this case)
import './styles/showcase.css';

window.addEventListener('load', () => {

    /* MOBILE MENU */

    const hamburgerMenu = document.getElementById("hamburger-menu");
    const mobileNav = document.getElementById("mobile-nav");
    const closeIcon = document.getElementById("close-icon");

    hamburgerMenu.addEventListener('click', () => {
    mobileNav.style.transform = "translateX(100%)";
    });

    closeIcon.addEventListener('click', () => {
    mobileNav.style.transform = "translateX(0)";
    });
    
    /* MOBILE MENU */
});