module.exports = {
    globDirectory: './public',
    swSrc: 'public/sw-offline.js',
    swDest: 'public/service-worker.js',
    maximumFileSizeToCacheInBytes: 4194304,
    globPatterns: ['**/*.{js,html,css,png,jpg,gif,svg,eot,svg,ttf,woff,woff2}'],
    globIgnores: ['**/*.map', 'mix-manifest.json', 'manifest.json', 'service-worker.js'],
};
