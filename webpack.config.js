const glob = require('glob-all');
const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const nodeModules = path.resolve(__dirname, 'node_modules');
const PurifyCSSPlugin = require('purifycss-webpack');
const AssetsCompressionPlugin = require('brotli-webpack-plugin');
const ScriptsCompressionPlugin = require('brotli-gzip-webpack-plugin');

const resources = path.resolve(__dirname, 'src');

const config = {
    cache: true,
    entry: {
        app: [
            './assets/app.js',
        ]
    },
    output: {
        library: '[name]', // assets build
        libraryTarget: 'umd',
        path: path.join(__dirname, 'public/compiled'),
        filename: '[name].js',
        chunkFilename: '[name]-[chunkhash].js'
    },
    module: {
        rules: [{
            // required for es6
            test: /\.js$/,
            exclude: /node_modules/,
            use: {
                loader: 'babel-loader',
                options: {
                    presets: ['env', 'react']
                }
            }
        }, {
            // required to write 'require('./style.scss')'
            test: /\.s(a|c)ss$/,
            loader: ExtractTextPlugin.extract({
                fallback: 'style-loader',
                use: [
                    {
                        loader: 'css-loader',
                        options: {
                            importLoaders: 1,
                            sourceMap: true
                        }
                    }, {
                        loader: 'postcss-loader',
                        options: {
                            sourceMap: true,
                            plugins: []
                        }
                    }, {
                        loader: 'sass-loader',
                        options: {
                            includePaths: [
                                path.resolve(resources , '/assets/'),
                                nodeModules
                            ],
                            sourceMap: true
                        }
                    },
                ]
            })
        }, {
            // required to write 'require('./style.css')'
            test: /\.css$/,
            loader: ExtractTextPlugin.extract({
                fallback: 'style-loader',
                use: [
                    {
                        loader: 'css-loader',
                        options: {
                            importLoaders: 1
                        }
                    },
                    'postcss-loader'
                ]
            })
        }, {
            // required for bootstrap icons
            test: /\.woff2?(\?v=\d+\.\d+\.\d+)?$/,
            loader: 'url-loader',
            options: {
                prefix: 'font/',
                limit: 5000,
                mimetype: 'application/font-woff'
            }
        }, {
            test: /\.(ttf|eot|svg|otf)(\?v=\d+\.\d+\.\d+)?$/,
            loader: 'file-loader',
            options: {
                prefix: 'font/'
            }
        }, {
            // required to support images
            test: /\.(jpe?g|png|gif|svg)$/i,
            use: [
                {
                    loader: 'file-loader',
                    options: {
                        hash: 'sha512',
                        digest: 'hex',
                        name: '[hash].[ext]'
                    }
                },
                {
                    loader: 'image-webpack-loader',
                    options: {
                        includePaths: [
                            path.resolve(resources , '/public/')
                        ],
                        gifsicle: {
                            interlaced: false
                        },
                        bypassOnDebug: true,
                        optipng: {
                            optimizationLevel: 7
                        }
                    }
                }
            ]
        }]
    },
    devtool: 'source-map',
    plugins: [
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify(process.env.NODE_ENV)
            }
        }),
        new webpack.NoEmitOnErrorsPlugin(),
        // Use the plugin to specify the resulting filename (and add needed behavior to the compiler)
        new ExtractTextPlugin({
            filename: '[name].css',
            allChunks: true
        })
    ]
};

if (process.env.NODE_ENV === 'production') {
    config.devtool= false;
    config.plugins.push(
        new webpack.LoaderOptionsPlugin({
            minimize: true
        }),
        new webpack.optimize.UglifyJsPlugin({
            sourceMap: true,
            compress: {
                // eslint-disable-next-line camelcase
                screw_ie8: true,
                warnings: false
            }
        }),
        // new PurifyCSSPlugin({
        //   minimize: true,
        //   paths: glob.sync([path.join(__dirname, 'src/.html'), path.join(__dirname, 'src/.js')]),
        // }),
        new AssetsCompressionPlugin({
            algorithm: 'gzip',
            asset: '[path].gz[query]',
            test: /\.(css|html|svg|ttf|eot|otf|woff|woff2|ico)$/,
            threshold: 10240,
            minRatio: 0.8,
        }),
        new ScriptsCompressionPlugin({
            algorithm: 'gzip',
            asset: '[path].gz[query]',
            test: /\.js$/,
            threshold: 10240,
            minRatio: 0.8,
        }),
        // optimize module ids by occurrence count
        new webpack.optimize.OccurrenceOrderPlugin()
    );
} else {
    config.plugins.push(
        new webpack.LoaderOptionsPlugin({
            debug: true
        })
    );
}

module.exports = config;