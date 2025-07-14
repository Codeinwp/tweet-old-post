module.exports = {
	root: true,
	env: {
		browser: true,
		node: true,
	},
	overrides: [
		// Vue files
		{
			files: ['vue/**/*.js', 'vue/**/*.vue'],
			extends: ['plugin:vue/recommended'],
			plugins: ['vue'],
			parserOptions: {
				parser: 'babel-eslint',
			},
			rules: {
				'vue/no-dupe-keys': 'warn',
				'vue/no-unused-components': 'warn',
				'vue/no-side-effects-in-computed-properties': 'warn',
				'vue/return-in-computed-property': 'warn',
				'vue/no-unused-vars': 'warn',
				'vue/no-textarea-mustache': 'warn',
				'vue/require-valid-default-prop': 'warn',
				'vue/multi-word-component-names': 'warn',
				'vue/no-mutating-props': 'warn',
			},
		},
		// React files
		{
			files: [ 'src/**/*.js' ],
			extends: ['plugin:@wordpress/eslint-plugin/recommended'],
			parserOptions: {
				ecmaFeatures: {
					jsx: true,
				},
				ecmaVersion: 'latest',
				sourceType: 'module',
			},
			rules: {
				'linebreak-style': ['error', 'unix'],
				'array-bracket-spacing': [
					'warn',
					'always',
					{
						arraysInArrays: false,
						objectsInArrays: false,
					},
				],
				'key-spacing': [
					'warn',
					{
						beforeColon: false,
						afterColon: true,
					},
				],
				'object-curly-spacing': [
					'warn',
					'always',
					{
						arraysInObjects: true,
						objectsInObjects: false,
					},
				],
				'@wordpress/i18n-text-domain': [
					'error',
					{
						allowedTextDomain: 'tweet-old-post',
					},
				],
				'@wordpress/no-unsafe-wp-apis': 0,
			},
		},
	],
};
