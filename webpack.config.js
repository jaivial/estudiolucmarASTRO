// webpack.config.js
const webpack = require('webpack');
const dotenv = require('dotenv');

dotenv.config();

module.exports = {
  // Other configurations...
  plugins: [
    new webpack.DefinePlugin({
      'process.env': JSON.stringify(process.env),
    }),
  ],
};
