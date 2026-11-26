import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

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
                display: ['"Instrument Sans"', ...defaultTheme.fontFamily.sans],
                sans: ['"Golos Text"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                canvas: 'var(--bg-canvas)',
                surface: 'var(--bg-surface)',
                tint: 'var(--bg-tint)',
                espresso: 'var(--bg-espresso)',
                ink: 'var(--text-primary)',
                'ink-muted': 'var(--text-secondary)',
                gold: 'var(--brand-gold)',
                'gold-text': 'var(--brand-gold-text)',
            },
        },
    },

    plugins: [forms],
};
