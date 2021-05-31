/**
 * Grunt File
 *
 * @package tweet-old-post
 */
 module.exports = function (grunt) {
	grunt.initConfig(
		{
			wp_readme_to_markdown: {
				files: {
					'readme.md': 'readme.txt'
				},
			},
			version: {
				project: {
					src: [
					'package.json'
					]
				},
				style: {
					options: {
						prefix: 'Version\\:\.*\\s'
					},
					src: [
					'tweet-old-post.php',
					]
				},
				functions: {
					options: {
						prefix: 'version\\s+=\\s+[\'"]'
					},
					src: [
					'includes/class-rop.php',
					]
				},
				constants: {
					options: {
						prefix: 'ROP_LITE_VERSION\'\,\\s+\''
					},
					src: [
					'tweet-old-post.php',
					]
				}
			}
		}
	);
	grunt.loadNpmTasks( 'grunt-version' );
	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
};