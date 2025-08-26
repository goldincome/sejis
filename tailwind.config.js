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
                pacifico: ['Pacifico', 'cursive'],
                roboto: ['Roboto', 'sans-serif'],
            },
            colors: {
              'brand-light-blue': '#D9E3FF',
              'brand-light-blue-darker': '#b8c9f0',
              'brand-deep-ash': '#36454F',
              'brand-deep-ash-lighter': '#4a5e6a',
              'brand-text-light': '#F3F4F6',
              'brand-text-dark': '#1F2937',
              'accent': '#F59E0B',
              'accent-darker': '#D97706',
              'star': '#ffc107', // Star color
            },
            animation: {
                'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                'scroll-x': 'scroll-x 20s linear infinite', // Animation for scrolling gallery
            },
            keyframes: {
                fadeInUp: {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'scroll-x': {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-50%)' }, // Translate by half the width for a seamless loop
                }
            }
        },
    },

    plugins: [forms],
};
