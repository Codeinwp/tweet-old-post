<template>
	<div class="toast" :class="toastTypeClass" >
		<button class="btn btn-clear float-right" @click="closeThis"></button>
		<b><i class="fa" :class="iconClass"></i> {{ toast.title }}</b><br/>
		<small>{{ toast.message }}</small>
	</div>
</template>

<script>
	module.exports = {
		name: 'toast',
		computed: {
			toast: function () {
				return this.$store.state.toast
			},
			toastTypeClass: function () {
				return {
					'toast-primary': this.toast.type === 'info',
					'toast-success': this.toast.type === 'success',
					'toast-warning': this.toast.type === 'warning',
					'toast-error': this.toast.type === 'error',
					'hidden': this.toast.show === false
				}
			},
			iconClass: function () {
				return {
					'fa-info-circle': this.toast.type === 'info',
					'fa-check-circle': this.toast.type === 'success',
					'fa-exclamation-triangle': this.toast.type === 'warning',
					'fa-exclamation-circle': this.toast.type === 'error'
				}
			}
		},
		mounted: function () {

		},
		created() {

		},
		methods: {
			closeThis () {
				this.toast.show = false
			}
		}
	}
</script>

<style>
	#rop_core .toast.hidden {
		display: none;
	}
	#rop_core .toast {
		margin: 10px 0;
	}
</style>