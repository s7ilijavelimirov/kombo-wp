const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const CopyWebpackPlugin = require("copy-webpack-plugin");
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const PurgeCSSPlugin = require('@fullhuman/postcss-purgecss');

module.exports = (env, argv) => {
    const isDev = argv.mode === 'development';

    return {
        entry: "./src/assets/scripts/main.js",
        output: {
            path: path.resolve(__dirname, "public/js"),
            filename: "script.js"
        },
        module: {
            rules: [
                {
                    test: /\.scss$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        'css-loader',
                        {
                            loader: 'postcss-loader',
                            options: {
                                postcssOptions: {
                                    plugins: [
                                        require('autoprefixer'),
                                        PurgeCSSPlugin({
                                            content: [
                                                './public/**/*.html',
                                                './src/**/*.js',
                                                './**/*.php'
                                            ],
                                            defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || []
                                        })
                                    ]
                                }
                            }
                        },
                        'sass-loader'
                    ]
                },
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-env']
                        }
                    }
                },
                {
                    test: /\.(woff|woff2|eot|ttf|otf)$/,
                    use: [
                        {
                            loader: 'file-loader',
                            options: {
                                outputPath: '../fonts',
                                publicPath: '../fonts'
                            }
                        }
                    ]
                }
            ]
        },
        plugins: [
            new CleanWebpackPlugin(),
            new MiniCssExtractPlugin({
                filename: isDev ? '../css/style.css' : '../css/style.[contenthash].css'
            }),
            new CopyWebpackPlugin({
                patterns: [
                    { from: 'src/assets/fonts', to: '../fonts' }
                ],
            })
        ],
        watchOptions: {
            ignored: /node_modules/
        },
        optimization: {
            minimize: !isDev,
            minimizer: [
                new TerserPlugin({
                    terserOptions: {
                        format: {
                            comments: false, // Remove all comments
                        },
                        compress: {
                            drop_console: true,
                        },
                    },
                    extractComments: false,
                }),
                new CssMinimizerPlugin({
                    minimizerOptions: {
                        preset: [
                            'default',
                            { discardComments: { removeAll: true } },
                        ],
                    },
                })
            ],
        },
        devtool: isDev ? 'eval-source-map' : false
    };
};
