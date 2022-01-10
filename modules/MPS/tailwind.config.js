module.exports = {
  mode: 'jit',
  purge: {
    enabled: true,
    content: ['./Resources/views/pdf/**/*.blade.php'],
  },
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {},
  },
  variants: {
    extend: {},
  },
  plugins: [],
  // corePlugins: {
  //   preflight: false,
  // },
};
