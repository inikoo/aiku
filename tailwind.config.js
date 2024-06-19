const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './resources/libraries/@protonemedia/inertiajs-tables-laravel-query-builder/**/*.{js,vue}',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                logo: ['Fira Sans',...defaultTheme.fontFamily.sans]
            },
            fontSize: {
                xxs: ['0.6rem', {
                    lineHeight: '0.8rem',
                }]
            },
            keyframes: {
                shimmer: {
                    '100%': {
                        transform: 'translateX(100%)',
                    },
                },
                errorShake: {
                    '0%, 100%': { transform: 'translateX(0)' },
                    '25%': { transform: 'translateX(-5px)' },
                    '50%': { transform: 'translateX(5px)' },
                    '75%': { transform: 'translateX(-5px)' },
                },
            },
            colors: {
                retina: {
                    700: 'rgb(15, 22, 38)',
                }
            },
            animation: {
                skeleton: 'shimmer 1.3s ease-in-out infinite',
                shimmer: 'shimmer 2.5s ease-in-out infinite',
                error_shake: 'errorShake 0.3s ease-in-out',
            }
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
