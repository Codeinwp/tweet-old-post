<template>
	<button class="btn" :class="is_active" @click="toggleThis()" >{{label}}</button>
</template>

<script>
	module.exports = {
		name: 'button-checkbox',
		props: {
			id: {
				default: ''
			},
			value: {
				default: '0',
				type: String
			},
			label: {
				default: '',
				type: String
			},
			checked: {
				default: false,
				type: Boolean
			}
		},
		data: function () {
			return {
				componentCheckState: this.checked
			}
		},
		computed: {
			is_active: function () {
				return {
					'active': this.componentCheckState === true
				}
			}
		},
		methods: {
			toggleThis () {
				this.componentCheckState = !this.componentCheckState
			}
		}
	}
</script>