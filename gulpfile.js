var elixir = require('laravel-elixir');

elixir.config.publicPath = 'web';
elixir.config.assetsPath = 'app/Resources/';

elixir(function(mix) {
    //Kompilace
    mix.sass('app.scss', 'app/Resources/css/app.css');

    //Kopirovani
    mix.copy('app/Resources/fonts', 'web/fonts/');
    mix.copy('app/Resources/tinymce', 'web/js/');
    mix.copy('app/Resources/syntaxhighlighter', 'web/syntaxhighlighter/');

    //Mergovani
    mix.stylesIn('app/Resources/css/');
    mix.scriptsIn('app/Resources/js/');
});