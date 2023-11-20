<template>
		<div :class="['snackbar', variation]">
				<i class="dashicons dashicons-info icon"></i>

				<div class="content">
					<slot></slot>
				</div>
				<button v-if="closeNotice" class="close" @click="closeNotice" :aria-label="close_label">
					<i class="dashicons dashicons-no-alt"></i>
				</button>
		</div>
</template>

<script>
/**
 * Component to display a notice in a snackbar.
 */
export default {
	name: 'snackbar-notice',
	props: {
		variation: {
			type: String,
			default: 'warning'
		},
		closeNotice: {
			type: Function,
			default: undefined
		}
	},
	data() {
		return {
			close_label: this.$store.state.labels.settings.close_notification
		}
	},
}
</script>

<style scoped>

	.warning {
		--icon-color: #DCA30E;
		--icon-bg-color: #FFEDCB;
		--close-btn-color: #E3B234;
		--close-btn-bg-color: transparent;
		--container-bg-color: #FFEDCB;
	}

	.snackbar {
		padding: 12.5px;
		gap: 12px;

		border-radius: 6.27px;
		border: 1px solid #C8AE6E;
		background-color: var(--container-bg-color);
	}

	.snackbar, button, .snackbar > * {
		display: flex;
		align-items: center;
	}

	.snackbar > i {
		color: var(--icon-color);
		font-size: 28px;
	}

	.snackbar > button {
		background-color: var(--close-btn-bg-color);
		border: none;
		cursor: pointer;
		outline: none;
		color: var(--close-btn-color);
		padding: 0;
	}

	button > i {
		font-size: 27px;
		width: 25px;
		height: 25px;
	}

	.content {
		flex-grow: 1;
		gap: 5px;
	}

	.snackbar:is(span, a) {
		color: var(--text-color);
		font-family: sans-serif;
		font-size: var(--text-size);
		font-style: normal;
		font-weight: 400;
		line-height: normal;

		margin: 0;
		padding: 0;
	}

	a {
		font-weight: var(--link-font-weight);
		text-decoration-line: underline;
	}
</style>
