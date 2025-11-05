const mix = require('laravel-mix');

mix.setPublicPath('public')
   .js('resources/js/notigen.js', 'public/js')
   .sass('resources/scss/notigen.scss', 'public/css')
   .version();
