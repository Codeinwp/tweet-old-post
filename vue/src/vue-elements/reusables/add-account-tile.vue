<template>
  <div>
    <div class="tile-content" />
    <div class="rop-add-account">
      <button
        v-if="!add_more_clicked && added_networks >= 2"
        class="btn btn-secondary"
        @click="addMore()"
      >
        <i class="fa fa-plus" />{{ labels.add_all_cta }}
      </button>

      <sign-in-btn v-if="add_more_clicked || added_networks < 2" />
    </div>
  </div>
</template>

<script>
	import SignInBtn from '../sign-in-btn.vue';

	export default {
		name: "AddAccountTile",
		components: {
			SignInBtn,
		},
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
