const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 */

mix.js('resources/js/app.js', 'public/js/main.js')
    .sass('resources/sass/app.scss', 'public/css/main.css');

if (mix.inProduction()) {
    mix.version();
} else {
    mix.sourceMaps();
}
