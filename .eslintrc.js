module.exports = {
    root: true,
    env: {
        browser: true,
        node: true
    },
    extends: 'plugin:vue/recommended',
    // required to lint *.vue files
    plugins: [
	    'vue',
    ],
    // add your custom rules here
    rules: {
        "vue/require-v-for-key": "warn",
        "vue/no-dupe-keys": "warn",
        "vue/no-use-v-if-with-v-for": "warn",
        "vue/no-unused-components": "warn",
        "vue/no-side-effects-in-computed-properties": "warn",
        "vue/return-in-computed-property": "warn",
        "vue/no-unused-vars": "warn",
        "vue/no-textarea-mustache": "warn",
        "vue/require-valid-default-prop": "warn",
        "vue/multi-word-component-names": "warn",
        "vue/no-mutating-props": "warn",
    },
    "parserOptions":{
	    "parser": "babel-eslint"
    },
    globals: {}
}