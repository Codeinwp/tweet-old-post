/* jshint node:true */
//https://github.com/kswedberg/grunt-version
module.exports = {
    options: {
        pkg: {
            version: '<%= package.version %>'
        }
    },
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
};