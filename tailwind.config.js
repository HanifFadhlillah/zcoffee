/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans:  ['DM Sans', 'sans-serif'],
                serif: ['DM Serif Display', 'serif'],
            },
            colors: {
                brand: {
                    DEFAULT: '#1C1917', // stone-900
                    accent:  '#F59E0B', // amber-500
                }
            },
        },
    },
    plugins: [],
}
