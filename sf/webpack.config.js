const Encore = require('@symfony/webpack-encore');
const WebpackPwaManifest = require('webpack-pwa-manifest');
const WorkboxPlugin = require('workbox-webpack-plugin');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

// define the pwa configuration
Encore
    .setOutputPath('public/')
    .setPublicPath('/')

    .addEntry('pwa', './assets/pwa.js')

    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild(['public'], (options) => {
        options.verbose = true;
        options.root = __dirname;
        options.exclude = ['images', '.htaccess', 'robots.txt', 'index.php'];
    })
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .addPlugin(
        new WebpackPwaManifest({
            filename: "manifest.webmanifest",
            name: '0Bot',
            short_name: '0Bot',
            description: 'ccxt-quant-bot',
            background_color: '#212529',
            inject: true,
            fingerprints: true,
            theme_color: '#212529',
            display: 'standalone',
            id: '/',
            scope: '/',
            start_url: '/',
            ios: {
                'apple-mobile-web-app-title': '0Bot',
                'apple-mobile-web-app-status-bar-style': 'black'
            },
            crossorigin: null, //can be null, use-credentials or anonymous
            icons: [
                {
                    src: 'public/images/bot192.png',
                    size: '192x192',
                    ios: true
                },
                {
                    src: 'public/images/bot256.png',
                    size: '256x256',
                    ios: true
                },
                {
                    src: 'public/images/bot512.png',
                    size: '512x512',
                    ios: true
                },
                {
                    src: 'public/images/bot1024.png',
                    size: '1024x1024',
                    ios: 'startup'
                },
                {
                    src: 'public/images/bot1024.png',
                    size: '1024x1024',
                    purpose: 'maskable'
                }
            ],
            screenshots: [
                {
                    "src": "images/screenshot.png",
                    "sizes": "1280x598",
                    "type": "image/png"
                },
            ]
        })
    )

    .addPlugin(
        new WorkboxPlugin.GenerateSW({
            // these options encourage the ServiceWorkers to get in there fast
            // and not allow any straggling "old" SWs to hang around
            clientsClaim: true,
            skipWaiting: true
        })
    )
;

// build the pwa configuration
const pwaConfig = Encore.getWebpackConfig();

// Set a unique name for the config (needed later!)
pwaConfig.name = 'pwaConfig';

// reset Encore to build the second config
Encore.reset();

// define the app configuration
Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    .setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

// build the main configuration
const appConfig = Encore.getWebpackConfig();

// Set a unique name for the config (needed later!)
appConfig.name = 'appConfig';

module.exports = [pwaConfig, appConfig];
