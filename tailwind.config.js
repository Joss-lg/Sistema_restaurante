/** @type {import('tailwindcss').Config} */
module.exports = {
  // AGREGA ESTA LÍNEA:
  // Le dice a Tailwind que busque la clase "dark" en un elemento padre (usualmente el html o body)
  darkMode: 'class', 

  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'luxury-bg': '#0a0a0c',
        'luxury-card': '#141417',
        'luxury-accent': '#3b82f6', 
        'luxury-border': 'rgba(255, 255, 255, 0.1)',
      },
    },
  },
  plugins: [],
}