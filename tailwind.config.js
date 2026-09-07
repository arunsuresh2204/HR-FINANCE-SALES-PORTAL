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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ink: {
                    950: '#050505',
                    900: '#0a0a0b',
                    850: '#101013',
                    800: '#17171b',
                    700: '#232328',
                    600: '#33333a',
                },
                gold: {
                    50: '#fef9e7',
                    100: '#fdf0c0',
                    200: '#fbe285',
                    300: '#f9d24a',
                    400: '#f7c81e',
                    500: '#f0bb0b',
                    600: '#cf9a06',
                    700: '#a67608',
                    800: '#875c0d',
                    900: '#714b10',
                },
            },
            backdropBlur: {
                xs: '2px',
            },
            boxShadow: {
                glass: '0 8px 32px 0 rgba(0, 0, 0, 0.45)',
                'glass-sm': '0 4px 16px 0 rgba(0, 0, 0, 0.35)',
                'glow-gold': '0 0 24px 0 rgba(247, 200, 30, 0.35)',
                'inset-glass': 'inset 0 1px 0 0 rgba(255,255,255,0.08)',
            },
            borderRadius: {
                '3xl': '1.75rem',
                '4xl': '2.25rem',
            },
        },
    },

    plugins: [forms],
};
