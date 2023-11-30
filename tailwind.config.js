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
            'beige': '#F6F5EF',
            'electric-blue': '#1B24E0',
        },
        // add new height of 7.125rem
        height: {
            '7.125': '1.75rem',
            '114': '42rem',
        },
        width: {
          '102' : '30rem',
          '110 ': '39rem',
          '162' : '90rem',
        }
    },
  },
  plugins: [],
}

