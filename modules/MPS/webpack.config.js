const path = require('path');
const is_production = process.env.NODE_ENV === 'production';

module.exports = {
  // output: {
  //   chunkFilename: is_production ? 'js/[name].[chunkhash].js' : 'js/[name].js',
  // },
  resolve: {
    alias: {
      '@mps': __dirname,
      '@mpsjs': __dirname + '/Resources/js',
      '@app': path.resolve(__dirname, '../..'),
      '@mpscom': __dirname + '/Resources/js/components',
      '@mlang': __dirname + '/../../resources/lang/modules/mps',
    },
  },
};
