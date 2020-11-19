const path = require("path");
const fs = require('fs');
const webpack = require('webpack');
const CopyPlugin = require('copy-webpack-plugin');

const dev = {
    port: 8099,
}

let vueConfig = {
    devServer: {
        host: '0.0.0.0',
        port: dev.port,
        public: `localhost:${dev.port}`,
        disableHostCheck: true,
        https: {key: fs.readFileSync('/etc/ssl/127.0.0.1-key.pem'), cert: fs.readFileSync('/etc/ssl/127.0.0.1.pem')},
    },
    publicPath: '/',
    outputDir: path.resolve(__dirname, './../front/dist'),
    configureWebpack: {
        output: {
            filename: '[name].js',
            chunkFilename: '[name].js'
        },
        plugins: [
            new webpack.optimize.LimitChunkCountPlugin({
                maxChunks: 1,
            }),
            new CopyPlugin({
                patterns: [
                    { from: './node_modules/ton-client-web-js/tonclient.wasm' }
                ],
            }),
        ],
    },
    chainWebpack: config => {
        if (config.plugins.has('extract-css')) {
            const extractCSSPlugin = config.plugin('extract-css')
            extractCSSPlugin && extractCSSPlugin.tap(() => [{
                filename: '[name].css',
                chunkFilename: '[name].css'
            }])
        }
    }
}

module.exports = vueConfig;
