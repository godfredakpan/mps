const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../public').mergeManifest();
mix.js(__dirname + '/Resources/js/app.js', '../../public/js/auth.js');
mix.postCss(__dirname + '/Resources/css/app.css', '../../public/css/auth.css', [require('tailwindcss')]);
