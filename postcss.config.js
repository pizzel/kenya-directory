import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';
import purgecss from '@fullhuman/postcss-purgecss';
import cssnano from 'cssnano';

const config = {
    plugins: [
        tailwindcss(),
        autoprefixer(),
    ],
};

if (process.env.NODE_ENV === 'production') {
    // ESM compatibility check for PurgeCSS
    const purgecssPlugin = purgecss.default || purgecss;

    config.plugins.push(
        purgecssPlugin({
            content: [
                './resources/views/**/*.blade.php',
                './resources/js/**/*.js',
                './storage/framework/views/*.php',
                './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
            ],
            defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || [],
            safelist: {
                standard: ['html', 'body', 'active', 'show', 'is-open'],
                deep: [/^swiper-/, /^fa-/, /^fab-/, /^fas-/, /^far-/, /^lightbox-/, /^gallery-/, /^sl-/, /^simple-lightbox-/],
                greedy: [/^flatpickr-/]
            }
        })
    );

    config.plugins.push(cssnano({ preset: 'default' }));
}

export default config;
