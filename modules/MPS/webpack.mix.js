const path = require('path');
const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../public').mergeManifest();

mix.webpackConfig(require('./webpack.config'));
// mix.webpackConfig({
//   output: {
//     chunkFilename: mix.inProduction() ? 'js/[name].[chunkhash].js' : 'js/[name].js',
//   },
//   // module: {
//   //   rules: [
//   //     {
//   //       test: /\.less$/,
//   //       loader: 'less-loader',
//   //       options: {
//   //         javascriptEnabled: true,
//   //       },
//   //     },
//   //   ],
//   // },
//   resolve: {
//     alias: {
//       '@mps': __dirname,
//       '@mpsjs': __dirname + '/Resources/js',
//       '@app': path.resolve(__dirname, '../..'),
//       '@mpscom': __dirname + '/Resources/js/components',
//       '@mlang': __dirname + '/../../resources/lang/modules/mps',
//     },
//   },
// });

mix.js(__dirname + '/Resources/js/app.js', '../../public/js/mps.js').vue({ version: 2 });
mix.sass(__dirname + '/Resources/sass/app.scss', '../../public/css/mps.css');
// mix.postCss(__dirname + '/Resources/css/pdf.css', '../../public/css/pdf.css', [require("tailwindcss")]);

if (mix.inProduction()) {
  mix.version();
  mix.copy(__dirname + '/Resources/css/**/*', '../../public/css/');
  // mix.copy(__dirname + '/Resources/sound/**/*', '../../public/');
  mix.less(__dirname + '/Resources/less/index.less', '../../public/css/mps_ui.css', { lessOptions: { javascriptEnabled: true } });
} else {
  mix.browserSync('mps.test').sourceMaps();
}

mix.disableNotifications();
