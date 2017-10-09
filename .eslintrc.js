module.exports = {
    root: true,
    parser: 'babel-eslint',
    env: {
        browser: true,
        node: true
    },
    extends: 'standard',
    // required to lint *.vue files
    plugins: [
        'html'
    ],
    // add your custom rules here
    rules: {
        "indent": ["error", "tab"],
        "no-tabs": 0,
        "space-in-parens": ["error", "always"],
        "camelcase": [2,{"properties":"never"}],
    },
    globals: {}
}