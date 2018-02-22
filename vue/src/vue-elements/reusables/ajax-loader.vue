<template>
    <div class="ajax-loader" :class="isVisible" >
        <i class="fa fa-spinner fa-spin"></i> <b>Loading ...</b>
    </div>
</template>

<script>
	module.exports = {
		name: 'ajax-loader',
		computed: {
			ajaxLoader: function () {
				return this.$store.state.ajaxLoader
			},
			isVisible: function () {
				return {
					'ajax-show': this.ajaxLoader === true,
					'ajax-hide': this.ajaxLoader === false
				}
			}
		},
		methods: {}
	}
</script>

<style>
    #rop_core .ajax-loader.ajax-hide {
        display: none;
    }
    #rop_core .ajax-loader.ajax-show {
        display: block;
    }
</style>