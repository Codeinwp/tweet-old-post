<template>
  <div id="rop-upsell-box">
    <div
      v-if="license < 1 "
      class="card rop-upsell-pro-card"
    >
      <a
        :href="getUpsellLink('pro')"
        target="_blank"
      >
        <img
          class="img-responsive"
          :src="to_pro_upsell"
          :alt="labels.upgrade_pro_cta"
        >
      </a>
    </div>
    <div
      v-if="license === 1 || license === 7 "
      class="card rop-upsell-business-card"
    >
      <a
        :href="getUpsellLink('business')"
        target="_blank"
      >
        <img
          class="img-responsive"
          :src="to_business_upsell"
          :alt="labels.upgrade_biz_cta"
        >
      </a>
    </div>
  </div>
</template>

<script>
	export default {
		name: "UpsellSidebar",
		data: function () {
			return {
				license: this.$store.state.licence,
				upsell_link: ropApiSettings.upsell_link,
				to_pro_upsell: ROP_ASSETS_URL + 'img/to_pro.png',
				labels: this.$store.state.labels.general,
				to_business_upsell: ROP_ASSETS_URL + 'img/to_business.png',
			}
		},

    methods: {
      getUpsellLink: function (type) {
        return wp.url.addQueryArgs(this.upsell_link, {utm_source: 'wpadmin', utm_medium: 'sidebar', utm_campaign: type});
      }
    }
	}
</script>

<style scoped>
	#rop-upsell-box{
		margin-top:20px;
	}
	#rop_core .rop-upsell-business-card,
	#rop_core .rop-upsell-pro-card {
		padding: 0;
	}
</style>