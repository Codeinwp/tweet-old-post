<template>
	<div>
		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.post_content_title}}</b>
				<p class="text-gray">{{labels.post_content_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<select class="form-select" v-model="post_format.post_content">
						<option value="post_title">{{labels.post_content_option_title}}</option>
						<option value="post_content">{{labels.post_content_option_content}}</option>
						<option value="post_title_content">{{labels.post_content_option_title_content}}</option>
						<option value="custom_field">{{labels.post_content_option_custom_field}}</option>
					</select>
				</div>
			</div>
		</div>
		<div class="columns py-2" v-if="post_format.post_content === 'custom_field'">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.custom_meta_title}}</b>
				<p class="text-gray">{{labels.custom_meta_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.custom_meta_field"
					       value="" placeholder=""/>
				</div>
			</div>
		</div>

		<span class="divider"></span>

		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.max_char_title}}</b>
				<p class="text-gray">{{labels.max_char_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="number" v-model="post_format.maximum_length"
					       value="" placeholder=""/>
				</div>
			</div>
		</div>
		<span class="divider"></span>

		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.add_char_title}}</b>
				<p class="text-gray">{{labels.add_char_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<textarea class="form-input" v-model="post_format.custom_text"
					          placeholder="">{{post_format.custom_text}}</textarea>
				</div>
			</div>
		</div>

		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<p class="text-gray">{{labels.add_pos_title}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<select class="form-select" v-model="post_format.custom_text_pos">
						<option value="beginning">{{labels.add_pos_option_start}}</option>
						<option value="end">{{labels.add_pos_option_end}}</option>
					</select>
				</div>
			</div>
		</div>
		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.add_link_title}}</b>
				<p class="text-gray">{{labels.add_link_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="input-group">
					<label class="form-checkbox">
						<input type="checkbox" v-model="post_format.include_link"/>
						<i class="form-icon"></i> {{labels.add_link_yes}}
					</label>
				</div>
			</div>
		</div>
		<span class="divider"></span>
		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.meta_link_title}}</b>
				<p class="text-gray">{{labels.meta_link_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="input-group">
					<label class="form-checkbox">
						<input type="checkbox" v-model="post_format.url_from_meta"/>
						<i class="form-icon"></i> {{labels.meta_link_yes}}
					</label>
				</div>
			</div>
		</div>
		<!-- Custom Field -->
		<div class="columns py-2" v-if="post_format.url_from_meta">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.meta_link_name_title}}</b>
				<p class="text-gray">{{labels.meta_link_name_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.url_meta_key" value=""
					       placeholder=""/>
				</div>
			</div>
		</div>
		<span class="divider"></span>
		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.use_shortner_title}}</b>
				<p class="text-gray">{{labels.use_shortner_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="input-group">
					<label class="form-checkbox">
						<input type="checkbox" v-model="post_format.short_url"/>
						<i class="form-icon"></i> {{labels.use_shortner_yes}}
					</label>
				</div>
			</div>
		</div>
		<div class="columns py-2" v-if="post_format.short_url">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.shortner_title}}</b>
				<p class="text-gray">{{labels.shortner_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<select class="form-select" v-model="post_format.short_url_service">
						<option value="rviv.ly">rviv.ly</option>
						<option value="bit.ly">bit.ly</option>
						<option value="goo.gl">goo.gl</option>
						<option value="ow.ly">ow.ly</option>
						<option value="is.gd">is.gd</option>
						<option value="wp_short_url">wp_short_url</option>
					</select>
				</div>
			</div>
		</div>

		<div class="columns py-2" v-if="post_format.short_url" v-for="( credential, key_name ) in post_format.shortner_credentials">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{ key_name | capitalize }}</b>
				<p class="text-gray">{{labels.shortner_field_desc_start}} "{{key_name}}"
					{{labels.shortner_field_desc_end}}
					<strong>{{post_format.short_url_service}}</strong> {{labels.shortner_api_field}}.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.shortner_credentials[key_name]">
				</div>
			</div>
		</div>

		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.hashtags_title}}</b>
				<p class="text-gray">{{labels.hashtags_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<select class="form-select" v-model="post_format.hashtags">
						<option value="no-hashtags">{{labels.hashtags_option_no}}</option>
						<option value="common-hashtags">{{labels.hashtags_option_common}}</option>
						<option value="categories-hashtags">{{labels.hashtags_option_cats}}</option>
						<option value="tags-hashtags">{{labels.hashtags_option_tags}}</option>
						<option value="custom-hashtags">{{labels.hashtags_option_field}}</option>
					</select>
				</div>
			</div>
		</div>
		<div class="columns py-2" v-if="post_format.hashtags === 'common-hashtags'">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.hastags_common_title}}</b>
				<p class="text-gray">{{labels.hastags_common_desc}} ",".</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.hashtags_common" value=""
					       placeholder=""/>
				</div>
			</div>
		</div>

		<div class="columns py-2" v-if="post_format.hashtags === 'custom-hashtags'">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.hastags_field_title}}</b>
				<p class="text-gray">{{labels.hastags_field_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.hashtags_custom" value=""
					       placeholder=""/>
				</div>
			</div>
		</div>

		<div class="columns py-2" v-if="post_format.hashtags !== 'no-hashtags'">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{labels.hashtags_length_title}}</b>
				<p class="text-gray">{{labels.hashtags_length_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="number" v-model="post_format.hashtags_length"
					       value="" placeholder=""/>
				</div>
			</div>
		</div>

		<span class="divider"></span>

		<div class="columns py-2" :class="'rop-control-container-'+isPro">
			<div class="column col-6 col-sm-12 vertical-align rop-control">
				<b>{{labels.image_title}}</b>
				<p class="text-gray">{{labels.image_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align rop-control">
				<div class="input-group">
					<label class="form-checkbox">
						<input type="checkbox" v-model="post_format.image"
						       :disabled="!isPro"/>
						<i class="form-icon"></i> {{labels.image_yes}}
					</label>
				</div>
				<p class="option-upsell" v-if="!isPro"><i class="fa fa-lock"></i> {{labels.image_upsell}}</p>
			</div>
		</div>
		<span class="divider"></span>
		<!-- Google Analytics -->
		<div class="columns py-2" :class="'rop-control-container-'+isPro">
			<div class="column col-6 col-sm-12 vertical-align rop-control">
				<b>{{labels.utm_campaign_medium}}</b>
				<p class="text-gray">{{labels.utm_campaign_medium_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align text-left rop-control">
				<div class="form-group">
						<input type="text" :disabled="!isPro" class="form-input" v-model="post_format.utm_campaign_medium" placeholder="social"/>
				</div>
			</div>
		</div>

		<div class="columns py-2" :class="'rop-control-container-'+isPro">
			<div class="column col-6 col-sm-12 vertical-align rop-control">
				<b>{{labels.utm_campaign_name}}</b>
				<p class="text-gray">{{labels.utm_campaign_name_desc}}</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align text-left rop-control">
				<div class="form-group">
						<input type="text" :disabled="!isPro" class="form-input" v-model="post_format.utm_campaign_name" placeholder="ReviveOldPost"/>
						<p class="option-upsell" v-if="!isPro"><i class="fa fa-lock"></i> {{labels.custom_utm_upsell}}</p>
				</div>
			</div>
		</div>

		<span class="divider"></span>
	</div>
</template>

<script>
	module.exports = {
		name: "post-format",
		props: ['account_id', 'license'],
		data: function () {
			return {
				labels: this.$store.state.labels.post_format,
				upsell_link: ropApiSettings.upsell_link,
			}
		},
		computed: {
			post_format: function () {
				return this.$store.state.activePostFormat[this.account_id] ? this.$store.state.activePostFormat[this.account_id] : [];
			},
			isPro: function () {
				return (this.license > 0);
			},
			short_url_service: function () {
				let postFormat = this.$store.state.activePostFormat[this.account_id] ? this.$store.state.activePostFormat[this.account_id] : [];
				return (postFormat.short_url_service) ? postFormat.short_url_service : '';
			}
		},
		watch: {
			short_url_service: function () {
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'get_shortner_credentials',
					data: {short_url_service: this.short_url_service}
				}).then(response => {
					this.post_format.shortner_credentials = response
				}, error => {
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			}
		},
		filters: {
			capitalize: function (value) {
				if (!value) return ''
				value = value.toString()
				return value.charAt(0).toUpperCase() + value.slice(1)
			}
		}
	}
</script>
<style scoped>
	#rop_core .panel-body .text-gray {
		margin: 0;
		line-height: normal;
	}

	b {
		margin-bottom: 5px;
		display: block;
	}

	#rop_core .input-group .input-group-addon {
		padding: 3px 5px;
	}

	@media ( max-width: 600px ) {
		#rop_core .panel-body .text-gray {
			margin-bottom: 10px;
		}

		#rop_core .text-right {
			text-align: left;
		}
	}
</style>
