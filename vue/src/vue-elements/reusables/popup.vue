<template>
	<div v-if="isVisible" class="popup">
		<div ref="popup" class="popup-content">
			<slot></slot>
		</div>
	</div>
</template>

<script>
/**
 * Component to display content into a popup.
 */
export default {
	name: 'Popup',
	props: {
		closeWhenClickedOutside: {
			type: Boolean,
			default: true
		}
	},
	data() {
		return {
			isVisible: false,
		};
	},
	methods: {
		openPopup() {
			this.isVisible = true;
		},
		closePopup() {
			this.isVisible = false;
			this.$emit('closed');
		},
		handleClickOutside(event) {
			if ( this.closeWhenClickedOutside && this.isVisible && !this.$refs.popup.contains(event.target)) {
				this.closePopup();
			}
		}
	},
	mounted() {
		document.addEventListener('click', this.handleClickOutside);
	},
	beforeDestroy() {
		document.removeEventListener('click', this.handleClickOutside);
	}
};
</script>

<style scoped>
.popup {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0, 0, 0, 0.5);
	display: flex;
	justify-content: center;
	align-items: center;
	z-index: 1000;
}

.popup-content {
	background: white;
	padding: 50px;
	border-radius: 7px;
	box-shadow: 0 2.5px 10px 0 rgba(0, 0, 0, 0.16);
	max-width: 600px;
}

@media (max-width: 600px) {
	.popup-content {
		padding: 15px;
	}
}
</style>
