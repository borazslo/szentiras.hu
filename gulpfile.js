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
        .sass(['app.scss'], 'resources/assets/css/compiled/sass.css')
        .less(['autocomplete.less'], 'resources/assets/css/compiled/less.css')
        .styles([
			'font-awesome.min.css',
            '../../../node_modules/jquery-ui/themes/base/minified/jquery-ui.min.css',
            'compiled/sass.css',
            'style.css',
            'compiled/less.css'
            ]);
    mix.
        coffee(['ad.coffee', 'pdfDialog.coffee', 'quickSearch.coffee', 'textDisplay.coffee', 'app.coffee'], 'resources/assets/js/compiled/coffee.js');
    mix.scripts(['compiled/coffee.js'], 'resources/assets/js/compiled/app.js');

    process.env.BROWSERIFYSHIM_DIAGNOSTICS=1;

    elixir.config.js.browserify.transformers.push({
        name: 'browserify-shim',
        options: {}
    });

    mix.browserify('resources/assets/js/compiled/app.js')

    mix.version(['css/all.css', 'js/app.js']);

    mix.copy('resources/assets/img', 'public/build/img');
    mix.copy('resources/assets/fonts', 'public/build/fonts');
    mix.copy('node_modules/bootstrap-sass/assets/fonts/bootstrap', 'public/build/fonts/bootstrap');
    mix.copy('node_modules/jquery-ui/themes/base/images', 'public/build/css/images');
    mix.copy('resources/assets/css/images', 'public/build/css/images');

});
