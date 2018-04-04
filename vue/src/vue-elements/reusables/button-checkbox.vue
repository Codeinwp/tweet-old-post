<template>
	<button class="btn input-group-addon column" :class="is_active" @click="toggleThis()" >{{label}}</button>
</template>

<script>
	module.exports = {
		name: 'button-checkbox',
		props: {
			value: {
				default: '0',
				type: String
			},
			label: {
				default: '',
				type: String
			},
			id: {
				default: function () {
					let base = 'day'
					if ( this.label !== '' && this.label !== undefined ) {
						base = base + '_' + this.label.toLowerCase()
					}

					return base
				}
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
		watch: {
			checked: function () {
				this.componentCheckState = this.checked
			}
		},
		methods: {
			toggleThis () {
				this.componentCheckState = !this.componentCheckState
				if ( this.componentCheckState ) {
					this.$emit( 'add-day', this.value )
				} else {
					this.$emit( 'rmv-day', this.value )
				}
			}
		}
	}
</script>
<style scoped>
	#rop_core .input-group .input-group-addon.btn.active {
		background-color: #8bc34a;
		border-color: #33691e;
		color: #FFF;
	}
</style>