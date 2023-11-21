/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
        colors: {
            'light-dark': '#1C1C1A',
            'light-white': '#F9F9F9',
        },
        // add new height of 7.125rem
        height: {
            '7.125': '1.75rem',
        },
    },
  },
  plugins: [],
}

