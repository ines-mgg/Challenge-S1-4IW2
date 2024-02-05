/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (showcase.css in this case)
import './styles/showcase.css';
import { startStimulusApp } from '@symfony/stimulus-bridge';

export const showcase = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!../controllers',
    true,
    /\.(j|t)sx?$/
));
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

    /* DARK MODE */

    const switchButton = document.querySelector("#switch-button");
    const switchToggle = document.querySelector("#switch-toggle");
    let isDarkmode = false;

    const darkIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><g fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M0 0h24v24H0z"/><path fill="#312e81" d="M12 1.992a10 10 0 1 0 9.236 13.838c.341-.82-.476-1.644-1.298-1.31a6.5 6.5 0 0 1-6.864-10.787l.077-.08c.551-.63.113-1.653-.758-1.653h-.266l-.068-.006z"/></g></svg>
    `;

    const lightIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256"><path fill="#fcd34d" d="M120 40V16a8 8 0 0 1 16 0v24a8 8 0 0 1-16 0m8 24a64 64 0 1 0 64 64a64.07 64.07 0 0 0-64-64m-69.66 5.66a8 8 0 0 0 11.32-11.32l-16-16a8 8 0 0 0-11.32 11.32Zm0 116.68l-16 16a8 8 0 0 0 11.32 11.32l16-16a8 8 0 0 0-11.32-11.32M192 72a8 8 0 0 0 5.66-2.34l16-16a8 8 0 0 0-11.32-11.32l-16 16A8 8 0 0 0 192 72m5.66 114.34a8 8 0 0 0-11.32 11.32l16 16a8 8 0 0 0 11.32-11.32ZM48 128a8 8 0 0 0-8-8H16a8 8 0 0 0 0 16h24a8 8 0 0 0 8-8m80 80a8 8 0 0 0-8 8v24a8 8 0 0 0 16 0v-24a8 8 0 0 0-8-8m112-88h-24a8 8 0 0 0 0 16h24a8 8 0 0 0 0-16"/></svg>
    `;


    const cookieValue = document.cookie
    .split("; ")
    .find((row) => row.startsWith("themeEnable"))
    ?.split("=")[1];

    switchButton.addEventListener('click', toggleTheme(cookieValue));


    function toggleTheme(cookieValue) {
    console.log("?", cookieValue);
    if(cookieValue == 'false') {
        document.cookie = "themeEnable=true;";
        switchTheme('true');
    }else {
        document.cookie = "themeEnable=false;";
        switchTheme('false');
    }
    }
    

    function switchTheme(cookieValue) {
        if (cookieValue == 'true') {
            document.documentElement.classList.add('dark')
            switchToggle.classList.remove("-translate-x-2");
            switchToggle.classList.add("translate-x-full");
            switchButton.classList.remove("bg-gray-200")
            switchButton.classList.add("bg-light-dark");
            setTimeout(() => {
                switchToggle.innerHTML = darkIcon;
            }, 250);
        } else {
            document.documentElement.classList.remove('dark')
            switchToggle.classList.add("-translate-x-2");
            switchToggle.classList.remove("translate-x-full");
            switchButton.classList.add("bg-gray-200")
            switchButton.classList.remove("bg-light-dark");
            setTimeout(() => {
                switchToggle.innerHTML = lightIcon;
            }, 250);
        }
    }

    switchTheme(cookieValue);

    /* DARK MODE */
});
