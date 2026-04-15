/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        // Aquí puedes definir tus colores "Luxury" para usarlos en todo el sitio
        'luxury-bg': '#0a0a0c',
        'luxury-card': '#141417',
        'luxury-accent': '#3b82f6', 
        'luxury-border': 'rgba(255, 255, 255, 0.1)',
      },
    },
  },
  plugins: [],
}