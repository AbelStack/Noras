/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        beige: {
          50: '#faf9f8',
          100: '#f5f3f0',
          200: '#ebe8e3',
          300: '#e0dbd4',
        },
        cream: '#f5f3f0',
        softOrange: '#ff9d76',
        // Dashboard colors matching the reference
        primary: {
          DEFAULT: '#2E5CCC', // Main blue
          50: '#EEF2FF',
          100: '#E0E7FF',
          500: '#2E5CCC',
          600: '#2548A6',
          700: '#1D3A80',
          800: '#172E66',
          900: '#0F1E42',
        },
        accent: {
          DEFAULT: '#FDB022', // Yellow/Orange
          500: '#FDB022',
          600: '#D9941C',
        },
      },
      fontFamily: {
        sans: ['Outfit', 'system-ui', '-apple-system', 'sans-serif'],
      },
      backdropBlur: {
        xs: '2px',
      }
    },
  },
  plugins: [],
}
