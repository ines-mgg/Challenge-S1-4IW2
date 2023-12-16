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
            'lilac': '#EFE9FE'
        },
        // add new height of 7.125rem
        height: {
            '7.125': '1.75rem',
            '103': '31rem',
            '114': '42rem',
            '120': '48rem',
            '122': '50rem',
            '4.625': '4.625rem'
        },
        width: {
          '7' : '25rem',
          '22.125': '22.125rem',
          '102' : '30rem',
          '105' : '33rem',
          '109' : '38rem',
          '110 ': '39rem',
          '118' : '47rem',
          '162' : '90rem',
        },
        boxShadow: {
          'custom-beige' : '12px 12px 0 -1px #FCF7DE, 12px 12px 0 0 #1C1C1A',
          'custom-purple' : '12px 12px 0 -1px #F2DFFC, 12px 12px 0 0 #1C1C1A',
          'custom-blue' : '12px 12px 0 -1px #2730FE, 12px 12px 0 0 #1C1C1A'
        },
    },
  },
  plugins: [],
}

