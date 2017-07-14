// webpack.config.js
var Encore = require('@symfony/webpack-encore');
var path = require('path');

Encore
// directory where all compiled assets will be stored
    .setOutputPath('web/build/')

    // what's the public path to this directory (relative to your project's document root dir)
    .setPublicPath('/build')

    .createSharedEntry('vendor', [
        'jquery'
    ])

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // will output as web/build/app.js
    .addEntry('app', './src/Adena/MailBundle/Resources/assets/js/main.js')

    // will output as web/build/global.css
    .addStyleEntry('global', './src/Adena/MailBundle/Resources/assets/css/global.scss')

    // allow sass/scss files to be processed
    .enableSassLoader()

    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()

    .enableSourceMaps(!Encore.isProduction())

    // create hashed filenames (e.g. app.abc123.css)
    .enableVersioning()

    .enableSassLoader(function(sassOptions) {}, {
                 resolve_url_loader: false
     })
;

var config = Encore.getWebpackConfig();
// config.resolve.alias.ace = path.resolve(__dirname, 'src/Adena/MailBundle/Resources/public/lib/ace/ace');

// export the final configuration
module.exports = config;