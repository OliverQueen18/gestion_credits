import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#e8f1f8',
                    100: '#c5d9ec',
                    200: '#9bbcdc',
                    300: '#6e9ccb',
                    400: '#3d7ab3',
                    500: '#1f5f9a',
                    600: '#164e82',
                    700: '#0e3f6c',
                    800: '#0a3358',
                    900: '#072544',
                    950: '#04182d',
                },
                accent: {
                    50: '#eefaf0',
                    100: '#d4f0d8',
                    200: '#a9e0b3',
                    500: '#2ea043',
                    600: '#248638',
                    700: '#1c6c2d',
                },
                gold: {
                    400: '#f0c43a',
                    500: '#e0b020',
                },
            },
        },
    },

    plugins: [forms],
};
