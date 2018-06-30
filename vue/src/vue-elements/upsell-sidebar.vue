<template>
	<div v-if="this.$store.state.cron_status.current_status" id="rop-upsell-box">
		<div class="card rop-upsell-pro-card" v-if="license  < 1 ">
			<a :href="upsell_link" target="_blank">
				<img class="img-responsive" :src="to_pro_upsell" :alt="labels.upgrade_pro_cta">
			</a>
		</div>
		<div class="card rop-upsell-business-card" v-if="license  === 1">
			<a :href="upsell_link" target="_blank">
				<img class="img-responsive" :src="to_business_upsell" :alt="labels.upgrade_biz_cta">
			</a>
		</div>
	</div>
</template>

<script>
	module.exports = {
		name: "upsell-sidebar",
		data: function () {
			return {
				license: this.$store.state.licence,
				upsell_link: ropApiSettings.upsell_link,
				to_pro_upsell: ROP_ASSETS_URL + 'img/to_pro.png',
				labels: this.$store.state.labels.general,
				to_business_upsell: ROP_ASSETS_URL + 'img/to_business.png',
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