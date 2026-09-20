import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ink: '#14161A',
                'ink-dark': '#EDEDEA',
                muted: '#6B7078',
                'muted-dark': '#93979F',
                paper: '#F6F6F4',
                'paper-dark': '#121316',
                surface: '#FFFFFF',
                'surface-dark': '#1B1C20',
                line: '#E5E5E1',
                'line-dark': '#2C2D31',
                accent: '#0C7C68',
                'accent-dark': '#1AAE93',
                positive: '#1C8A5B',
                'positive-dark': '#34B378',
                negative: '#C63C28',
                'negative-dark': '#F0665A',
            },
        },
    },

    plugins: [forms],
};
