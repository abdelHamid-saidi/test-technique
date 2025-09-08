const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),
    ]).webpackConfig({
        module: {
            rules: [
                {
                    test: /\.(postcss)$/,
                    use: [
                        'vue-style-loader',
                        { loader: 'css-loader', options: { importLoaders: 1 } },
                        'postcss-loader'
                    ]
                }
            ],
        },
        resolve: {
            alias: {
                'vue': 'vue/dist/vue.esm-bundler.js'
            }
        },
        // résoudre l'avertissement des feature flags Vue.
        plugins: [
            new (require('webpack')).DefinePlugin({
                __VUE_OPTIONS_API__: JSON.stringify(true),
                __VUE_PROD_DEVTOOLS__: JSON.stringify(false),
                __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false)
            })
        ]
    })
    .alias({
        '@': 'resources/js',
        ziggy: "vendor/tightenco/ziggy/dist/vue",
    }).vue({ version: 3 });

if (mix.inProduction()) {
    mix.version();
}
