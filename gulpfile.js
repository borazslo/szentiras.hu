process.env.DISABLE_NOTIFIER = true;

var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix
        .less(['*.less'], 'resources/assets/css/compiled/less.css')
        .styles([
			'font-awesome.min.css',
            'style.css',
            'compiled/less.css'
            ]);
    mix.
        coffee(['ad.coffee', 'pdfDialog.coffee', 'quickSearch.coffee', 'textDisplay.coffee'], 'resources/assets/js/compiled/coffee.js');
    mix.scripts(['compiled/coffee.js']);

    mix.version(['css/all.css', 'js/all.js']);

    mix.copy('resources/assets/img', 'public/build/img');
    mix.copy('resources/assets/fonts', 'public/build/fonts');
    mix.copy('resources/assets/css/images', 'public/build/css/images');

});
