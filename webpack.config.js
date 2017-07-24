// webpack.config.js
const Encore = require('@symfony/webpack-encore');
const path = require('path');

Encore
    // directory where all compiled assets will be stored
    .setOutputPath('web/build/')

    // what's the public path to this directory (relative to your project's document root dir)
    .setPublicPath('/build')

    .createSharedEntry('vendor', [
        'jquery',
        'bootstrap-sass',
        'brace',
        'brace/mode/twig',
        'brace/theme/monokai',
        'eonasdan-bootstrap-datetimepicker',
        'watchjs'
    ])

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // will output as web/build/app.js
    .addEntry('app', './app/Resources/assets/js/main.js')

    // will output as web/build/global.css
    .addStyleEntry('global', './app/Resources/assets/css/global.scss')

    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()

    .enableSourceMaps(!Encore.isProduction())

    // create hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .enableSassLoader(function(sassOptions) {}, {
        resolve_url_loader: false
    })
;

let config = Encore.getWebpackConfig();
config.resolve.alias.src = path.resolve(__dirname, 'src');
config.resolve.alias.vendor = path.resolve(__dirname, 'vendor');

// export the final configuration
module.exports = config;