<template>
	<div>
		<div class="tile-content">
		</div>
		<div class="rop-add-account">
     <button class="btn btn-secondary" v-if="!this.add_more_clicked && this.added_networks >= 2" @click="addMore()">
        <i class="fa fa-plus"></i>{{labels.add_all_cta}}
     </button>

					<sign-in-btn  v-if="this.add_more_clicked || this.added_networks < 2"></sign-in-btn>
		</div>
	</div>
</template>

<script>
	import SignInBtn from '../sign-in-btn.vue';

	module.exports = {
		name: "add-account-tile",
		data: function () {
			return {
				addAccountActive: false,
				labels: this.$store.state.labels.accounts,
				upsell_link: ropApiSettings.upsell_link,
    add_more_clicked: false,
    added_networks: ropApiSettings.added_networks,
			}
		},
		computed: {
		},
		components: {
			SignInBtn,
		},
  methods: {
    addMore: function () {
        this.add_more_clicked = true;
    },
  }
	}
</script>

<style scoped>
	.icon_box {
		background: #efefef;
		padding: 0;
		transition: .3s ease;
	}

	.icon_box.close .fa {
		line-height: 1.6em;
	}

	.icon_box.open .fa {
		line-height: 1.7em;
		width: 20px;
		transform: rotate(-135deg);
		-webkit-transform: rotate(-135deg);
	}

	.fa {
		transition: all .3s cubic-bezier(.34, 1.61, .7, 1);
	}

</style>
