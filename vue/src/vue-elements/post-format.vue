<template>
  <div>
    <div
      v-if="wpml_active_status"
      class="columns py-2"
    >
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.language_title }}</b>
        <p class="text-gray">
          {{ labels.language_title_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group">
          <select
            id="wpml-language-selector"
            v-model="post_format.wpml_language"
            class="form-select"
            :disabled="!isPro"
            @change="refresh_language_taxonomies"
          >
            <option
              v-for="(lang, index) in wpml_languages"
              :key="index"
              :value="lang.code"
              :selected="index == 0 || lang.code == post_format.wpml_language ? true : false"
            >
              {{ lang.label }}
            </option>
          </select>
        </div>
      </div>
    </div>
    <div
      v-if="!isPro && wpml_active_status"
      class="columns "
    >
      <div class="column text-center">
        <p class="upsell">
          <i class="fa fa-info-circle" /> <span v-html="labels.full_wpml_support_upsell" />
        </p>
      </div>
    </div>
    <span class="divider" />

    <div class="columns py-2">
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.post_content_title }}</b>
        <p class="text-gray">
          {{ labels.post_content_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group">
          <select
            v-model="post_format.post_content"
            class="form-select"
          >
            <option value="post_title">
              {{ labels.post_content_option_title }}
            </option>
            <option value="post_content">
              {{ labels.post_content_option_content }}
            </option>
            <option value="post_title_content">
              {{ labels.post_content_option_title_content }}
            </option>
            <option value="post_excerpt">
              {{ labels.post_content_option_excerpt }}
            </option>
            <option value="custom_field">
              {{ labels.post_content_option_custom_field }} {{ isNewUserPro ? "(Pro)" : '' }}
            </option>
            <option
              v-if="yoast_seo_active_status"
              value="yoast_seo_title"
              :disabled="!isPro"
            >
              {{ labels.post_content_option_yoast_seo_title }} {{ !isPro ? "(Pro)" : '' }}
            </option>
            <option
              v-if="yoast_seo_active_status"
              value="yoast_seo_description"
              :disabled="!isPro"
            >
              {{ labels.post_content_option_yoast_seo_description }} {{ !isPro ? "(Pro)" : '' }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <div
      v-if="post_format.post_content === 'custom_field'"
      class="columns py-2"
      :class="'rop-control-container-'+(!isNewUserPro)"
    >
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <b>{{ labels.custom_meta_title }}</b>
        <p class="text-gray">
          {{ labels.custom_meta_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <div class="form-group">
          <input
            v-model="post_format.custom_meta_field"
            class="form-input"
            type="text"
            value=""
            placeholder=""
            :disabled="!!isNewUserPro"
          >
        </div>
      </div>
    </div>
    <div
      v-if="!!isNewUserPro && (post_format.post_content === 'custom_field')"
      class="columns "
    >
      <div class="column text-center">
        <p class="upsell">
          <i class="fa fa-info-circle" /> {{ labels.custom_meta_field_upsell }}
        </p>
      </div>
    </div>

    <span class="divider" />

    <div class="columns py-2">
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.max_char_title }}</b>
        <p class="text-gray">
          {{ labels.max_char_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group">
          <input
            v-if="allAccounts[account_id].service === 'twitter'"
            v-model="post_format.maximum_length"
            class="form-input"
            type="number"
            value=""
            max="280"
          >
          <input
            v-if="allAccounts[account_id].service !== 'twitter'"
            v-model="post_format.maximum_length"
            class="form-input"
            type="number"
            value=""
            placeholder=""
          >
        </div>
        <p
          v-if="allAccounts[account_id].service === 'twitter'"
          v-html="labels.twitter_max_characters_notice"
        />
      </div>
    </div>
    <span class="divider" />

    <div class="columns py-2">
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.add_char_title }}</b>
        <p class="text-gray">
          <span v-html="labels.add_char_desc" />
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group">
          <textarea
            v-model="post_format.custom_text"
            class="form-input"
            :placeholder="labels.add_char_placeholder"
          />
        </div>
      </div>
    </div>

    <div class="columns py-2">
      <div class="column col-6 col-sm-12 vertical-align">
        <p class="text-gray">
          {{ labels.add_pos_title }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group">
          <select
            v-model="post_format.custom_text_pos"
            class="form-select"
          >
            <option value="beginning">
              {{ labels.add_pos_option_start }}
            </option>
            <option value="end">
              {{ labels.add_pos_option_end }}
            </option>
          </select>
        </div>
      </div>
    </div>
    <span class="divider" />
    <div class="columns py-2">
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.add_link_title }}</b>
        <p class="text-gray">
          {{ labels.add_link_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="input-group">
          <label class="form-checkbox">
            <input
              v-model="post_format.include_link"
              type="checkbox"
            >
            <i class="form-icon" /> {{ labels.yes_text }}
          </label>
        </div>
        <p
          v-if="allAccounts[account_id].account_type === 'instagram_account'"
          v-html="labels.instagram_disable_link_recommendation"
        />
      </div>
    </div>
    <span class="divider" />
    <div
      class="columns py-2"
      :class="'rop-control-container-'+(!isNewUserPro)"
    >
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <b>{{ labels.meta_link_title }}</b>
        <p class="text-gray">
          {{ labels.meta_link_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <div class="input-group">
          <label class="form-checkbox">
            <input
              v-model="post_format.url_from_meta"
              type="checkbox"
              :disabled="!!isNewUserPro"
            >
            <i class="form-icon" /> {{ labels.yes_text }}
          </label>
        </div>
      </div>
    </div>
    <div
      v-if="!!isNewUserPro"
      class="columns "
    >
      <div class="column text-center">
        <p class="upsell">
          <i class="fa fa-info-circle" /> {{ labels.custom_meta_upsell }}
        </p>
      </div>
    </div>

    <!-- Custom Field -->
    <div
      v-if="post_format.url_from_meta"
      class="columns py-2"
    >
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.meta_link_name_title }}</b>
        <p class="text-gray">
          {{ labels.meta_link_name_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group">
          <input
            v-model="post_format.url_meta_key"
            class="form-input"
            type="text"
            value=""
            placeholder=""
          >
        </div>
      </div>
    </div>
    <span class="divider" />
    <!-- License price id 7 is starter plan. Per account based filtering not included in starter plan,  -->
    <div
      class="columns py-2"
      :class="'rop-control-container-'+(isPro && (license_price_id !== 7))"
    >
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <b>{{ labels_settings.taxonomies_title }}</b>
        <p class="text-gray">
          <span v-html="labels_settings.taxonomies_desc" />
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="input-group">
          <multiple-select
            :key="account_id"
            :disabled="!!isPro && (license_price_id !== 7)"
            :options="taxonomy"
            :selected="taxonomy_filter"
            :name="post_format.taxonomy_filter"
            :changed-selection="updated_tax_filter"
          />
          <span class="input-group-addon vertical-align">
            <label class="form-checkbox">
              <input
                v-model="post_format.exclude_taxonomies"
                :disabled="!isPro || (license_price_id === 7)"
                type="checkbox"
              >
              <i class="form-icon" />{{ labels_settings.taxonomies_exclude }}
            </label>
          </span>
        </div>
      </div>
    </div>
    <div
      v-if="!isPro || (license_price_id === 7)"
      class="columns "
    >
      <div class="column text-center">
        <p class="upsell">
          <i class="fa fa-info-circle" /> {{ labels.taxonomy_based_sharing_upsell }}
        </p>
      </div>
    </div>

    <span class="divider" />
        
    <div class="columns py-2">
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.use_shortner_title }}</b>
        <p class="text-gray">
          {{ labels.use_shortner_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="input-group">
          <label class="form-checkbox">
            <input
              v-model="post_format.short_url"
              type="checkbox"
            >
            <i class="form-icon" /> {{ labels.yes_text }}
          </label>
        </div>
        <p
          v-if="allAccounts[account_id].service === 'vk'"
          v-html="labels.vk_unsupported_shorteners"
        />
      </div>
    </div>

    <div
      v-if="post_format.short_url"
      class="columns py-2"
    >
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.shortner_title }}</b>
        <p class="text-gray">
          {{ labels.shortner_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group">
          <select
            v-model="post_format.short_url_service"
            class="form-select"
          >
            <option
              v-for="shortener in shorteners"
              :key="shortener.id"
              :value="isNewUserPro && ( 'is.gd' === shortener.id ) ? '' : shortener.id"
              :disabled="shortener.active !== true"
              :selected="shortener.name == post_format.short_url_service"
            >
              {{ shortener.name }}{{ (isNewUserPro && shortener.is_free === false) || shortener.active !== true ? " (Pro)" : '' }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <template v-if="post_format.short_url">
      <div
        v-for="( credential, key_name ) in post_format.shortner_credentials"
        :key="key_name"
        :class="'rop-control-container-'+(!isNewUserPro || (post_format.short_url_service === 'rviv.ly' || post_format.short_url_service === 'wp_short_url'))"
        class="columns py-2"
      >
        <div class="column col-6 col-sm-12 vertical-align rop-control">
          <b>{{ key_name | capitalize }}</b>
          <p class="text-gray">
            {{ labels.shortner_field_desc_start }} "{{ key_name }}"
            {{ labels.shortner_field_desc_end }}
            <strong>{{ post_format.short_url_service }}</strong> {{ labels.shortner_api_field }}.
          </p>
        </div>
        <div class="column col-6 col-sm-12 vertical-align rop-control">
          <div class="form-group">
            <input
              v-model="post_format.shortner_credentials[key_name]"
              class="form-input"
              type="text"
              :disabled="!!isNewUserPro && (post_format.short_url_service !== 'rviv.ly' || post_format.short_url_service !== 'wp_short_url')"
            >
          </div>
        </div>
      </div>
      <div
      v-if="!!isNewUserPro && (post_format.short_url_service !== 'rviv.ly' && post_format.short_url_service !== 'wp_short_url')"
      class="columns "
    >
      <div class="column text-center">
        <p class="upsell">
          <i class="fa fa-info-circle" /> {{ labels.hashtag_field_upsell }}
        </p>
      </div>
    </div>
    </template>
    

    <span class="divider" />

    <div class="columns py-2">
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.hashtags_title }}</b>
        <p class="text-gray">
          {{ labels.hashtags_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group">
          <select
            v-model="post_format.hashtags"
            class="form-select"
          >
            <option value="no-hashtags">
              {{ labels.hashtags_option_no }}
            </option>
            <option value="common-hashtags">
              {{ labels.hashtags_option_common }}
            </option>
            <option value="categories-hashtags">
              {{ labels.hashtags_option_cats }} {{ isNewUserPro ? "(Pro)" : '' }}
            </option>
            <option value="tags-hashtags">
              {{ labels.hashtags_option_tags }} {{ isNewUserPro ? "(Pro)" : '' }}
            </option>
            <option value="custom-hashtags">
              {{ labels.hashtags_option_field }} {{ isNewUserPro ? "(Pro)" : '' }}
            </option>
          </select>
        </div>
      </div>
    </div>
    <div
      v-if="post_format.hashtags === 'common-hashtags'"
      class="columns py-2"
    >
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.hastags_common_title }}</b>
        <p class="text-gray">
          {{ labels.hastags_common_desc }} ",".
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group">
          <input
            v-model="post_format.hashtags_common"
            class="form-input"
            type="text"
            value=""
            placeholder=""
          >
        </div>
      </div>
    </div>

    <div
      v-if="post_format.hashtags === 'custom-hashtags'"
      class="columns py-2"
      :class="'rop-control-container-'+(!isNewUserPro && (post_format.hashtags !== 'common-hashtags'))"
    >
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <b>{{ labels.hastags_field_title }}</b>
        <p class="text-gray">
          {{ labels.hastags_field_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <div class="form-group">
          <input
            v-model="post_format.hashtags_custom"
            class="form-input"
            type="text"
            value=""
            placeholder=""
            :disabled="!!isNewUserPro && (post_format.hashtags !== 'common-hashtags')"
          >
        </div>
      </div>
    </div>

    <div
      v-if="post_format.hashtags !== 'no-hashtags'"
      class="columns py-2"
      :class="'rop-control-container-'+(!isNewUserPro || (post_format.hashtags === 'no-hashtags' || post_format.hashtags === 'common-hashtags'))"
    >
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <b>{{ labels.hashtags_length_title }}</b>
        <p class="text-gray">
          {{ labels.hashtags_length_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <div class="form-group">
          <input
            v-model="post_format.hashtags_length"
            class="form-input"
            type="number"
            value=""
            placeholder=""
            :disabled="!!isNewUserPro && (post_format.hashtags !== 'no-hashtags' && post_format.hashtags !== 'common-hashtags')"
          >
        </div>
      </div>
    </div>

    <div
      v-if="post_format.hashtags !== 'no-hashtags'"
      class="columns py-2"
      :class="'rop-control-container-'+(!isNewUserPro || (post_format.hashtags === 'no-hashtags' || post_format.hashtags === 'common-hashtags'))"
    >
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <b>{{ labels.hashtags_randomize }}</b>
        <p class="text-gray">
          <span v-html="labels.hashtags_randomize_desc" />
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <div class="input-group">
          <label class="form-checkbox">
            <input
              v-model="post_format.hashtags_randomize"
              type="checkbox"
              :disabled="!!isNewUserPro && (post_format.hashtags !== 'no-hashtags' && post_format.hashtags !== 'common-hashtags')"
            >
            <i class="form-icon" /> {{ labels.yes_text }}
          </label>
        </div>
      </div>
    </div>
    <div
      v-if="!!isNewUserPro && (post_format.hashtags !== 'no-hashtags' && post_format.hashtags !== 'common-hashtags')"
      class="columns "
    >
      <div class="column text-center">
        <p class="upsell">
          <i class="fa fa-info-circle" /> {{ labels.hashtag_field_upsell }}
        </p>
      </div>
    </div>

    <span class="divider" />

    <div
      class="columns py-2"
      :class="'rop-control-container-'+isPro"
    >
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <b>{{ labels.image_title }}</b>
        <p class="text-gray">
          <span
            v-if="is_twitter && is_sharing_post_via_rop_server"
            class="block"
          >
            {{ labels.not_available_with_rop_server }}
          </span>
          <span v-html="labels.image_desc" />
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="input-group">
          <label class="form-checkbox">
            <input
              v-if="!is_instagram_account"
              v-model="post_format.image"
              type="checkbox"
              :disabled="!isPro || (is_twitter && is_sharing_post_via_rop_server)"
            >
            <!-- For instagram accounts -->
            <input
              v-if="is_instagram_account"
              v-model="is_instagram_account"
              type="checkbox"
              :disabled="!isPro || is_instagram_account"
            >
            <i class="form-icon" /> {{ labels.yes_text }}
          </label>
        </div>
        <p
          v-if="is_instagram_account"
          v-html="labels.instagram_image_post_default"
        />
      </div>
    </div>

    <span
      v-if="is_instagram_account"
      class="divider"
    />

    <div
      v-if="is_instagram_account"
      class="columns py-2"
      :class="'rop-control-container-'+isPro"
    >
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <b>{{ labels.image_aspect_ratio_title }}</b>
        <p class="text-gray">
          <span v-html="labels.image_aspect_ratio_title_desc" />
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="input-group">
          <label class="form-checkbox">
            <input
              v-if="is_instagram_account"
              v-model="post_format.correct_aspect_ratio"
              type="checkbox"
            >
            <i class="form-icon" /> {{ labels.yes_text }}
          </label>
        </div>
      </div>
    </div>

    <div
      v-if="!isPro"
      class="columns "
    >
      <div class="column text-center">
        <p class="upsell">
          <i class="fa fa-info-circle" /> {{ labels.image_upsell }}
        </p>
      </div>
    </div>
    <span class="divider" />
    <!-- Google Analytics -->
    <div
      class="columns py-2"
      :class="'rop-control-container-'+isPro"
    >
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <b>{{ labels.utm_campaign_medium }}</b>
        <p class="text-gray">
          {{ labels.utm_campaign_medium_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
        <div class="form-group">
          <input
            v-model="post_format.utm_campaign_medium"
            type="text"
            :disabled="!isPro"
            class="form-input"
            placeholder="social"
          >
        </div>
      </div>
    </div>

    <div
      class="columns py-2"
      :class="'rop-control-container-'+isPro"
    >
      <div class="column col-6 col-sm-12 vertical-align rop-control">
        <b>{{ labels.utm_campaign_name }}</b>
        <p class="text-gray">
          {{ labels.utm_campaign_name_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
        <div class="form-group">
          <input
            v-model="post_format.utm_campaign_name"
            type="text"
            :disabled="!isPro"
            class="form-input"
            placeholder="ReviveOldPost"
          >
        </div>
      </div>
    </div>
    <div
      v-if="!isPro"
      class="columns "
    >
      <div class="column text-center">
        <p class="upsell">
          <i class="fa fa-info-circle" /> {{ labels.custom_utm_upsell }}
        </p>
      </div>
    </div>
    <span class="divider" />
  </div>
</template>

<script>
    import MultipleSelect from './reusables/multiple-select.vue'

    export default {
        name: "PostFormat",

        filters: {
            capitalize: function (value) {
                if (!value) return '';
                value = value.toString().split('_');
                var name = '';
                for (var x = 0; x < value.length; x++) {
                    name += value[x].charAt(0).toUpperCase() + value[x].slice(1) + ' ';
                }
                return name;
            }
        },
        components: {
            MultipleSelect
        },
        props: ['account_id', 'license'],
        data: function () {
            return {
                labels: this.$store.state.labels.post_format,
                labels_settings: this.$store.state.labels.settings,
                labels_generic: this.$store.state.labels.generic,
                upsell_link: ropApiSettings.upsell_link,
                wpml_active_status: ropApiSettings.rop_get_wpml_active_status,
                yoast_seo_active_status: ropApiSettings.rop_get_yoast_seo_active_status,
                wpml_languages: ropApiSettings.rop_get_wpml_languages,
                selected_tax_filter: [],
                // selected_language: this.$store.state.activePostFormat[this.account_id] ? this.$store.state.activePostFormat[this.account_id].wpml_language : [],
                // post_types: this.$store.state.generalSettings.available_post_types,
            }
        },
        computed: {

            allAccounts: function(){

                    const all_accounts = {};
                                
                    const services = this.$store.state.authenticatedServices;

                    for (const key in services) {
                        if (!services.hasOwnProperty(key)) {
                            continue;
                        }
                        const service = services[key];

                        for (const account_id in service.available_accounts) {
                            if (!service.available_accounts.hasOwnProperty(account_id)) {
                                continue;
                            }
                            all_accounts[account_id] = service.available_accounts[account_id];

                            if ( service?.credentials?.rop_auth_token ) {
                                all_accounts[account_id].is_using_rop_server = true;
                            }
                        }
                    }

                    return all_accounts;
            },
            is_instagram_account: function(){
                return this.allAccounts[this.account_id].account_type === 'instagram_account';
            },
            is_twitter: function(){
                return this.allAccounts[this.account_id].service === 'twitter';
            },
            is_sharing_post_via_rop_server: function(){
                return this.allAccounts[this.account_id]?.is_using_rop_server;
            },
            postTypes: function () {
                return this.$store.state.generalSettings.available_post_types;
            },
            post_format: function () {
                return this.$store.state.activePostFormat[this.account_id] ? this.$store.state.activePostFormat[this.account_id] : [];
            },
            isPro: function () {
                return (this.license > 0);
            },
            license_price_id: function () {
                return this.license;
            },
            short_url_service: function () {
                let postFormat = this.$store.state.activePostFormat[this.account_id] ? this.$store.state.activePostFormat[this.account_id] : [];
                return (postFormat.short_url_service) ? postFormat.short_url_service : '';
            },
            taxonomy_filter: function(){
                let postFormat = this.$store.state.activePostFormat[this.account_id] ? this.$store.state.activePostFormat[this.account_id] : [];
                if(postFormat.taxonomy_filter){
                    let index = 0;
                    for (let option of postFormat.taxonomy_filter) {
                        postFormat.taxonomy_filter[index].selected = true;
                        index++
                    }

                }

                return (postFormat.taxonomy_filter) ? postFormat.taxonomy_filter : [];
            },
            taxonomy: function () {
                return this.$store.state.generalSettings.available_taxonomies
            },
            shorteners: function () {
                return this.$store.state.generalSettings.available_shorteners;
            },
            isNewUserPro: function () {
                return 0 === this.license && this.$store.state.is_new_user;
            },
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
        created: function () {
            this.get_taxonomy_list();
        },
        updated: function() {
            this.$nextTick(function () {
                if(!this.$store.state.dom_updated){
                    if(this.wpml_active_status){
                    this.refresh_language_taxonomies();
                    }
                }
            });
        },
       methods:{
            refresh_language_taxonomies: function(e){
               
                if( this.wpml_active_status !== true){
                    return;
                }

                const lang = e && e.target ? e.target.options[e.target.options.selectedIndex].value : document.querySelector('#wpml-language-selector').value;
                if(e && e.target){
                    // clear selected taxonomies on language change
                    this.post_format.taxonomy_filter = [];
                }
                if(lang !== ''){
                    this.$store.dispatch('fetchAJAXPromise', {req: 'get_taxonomies', data: {post_types: this.postTypes, language_code: lang}});
                }
                this.$store.state.dom_updated = true;
            },
            get_taxonomy_list(){
                if (this.$store.state.generalSettings.length === 0) {
                    this.is_loading = true;
                    this.$log.info('Fetching general settings.');
                    this.$store.dispatch('fetchAJAXPromise', {req: 'get_general_settings'}).then(response => {
                        this.is_loading = false;
                        this.$log.debug('Successfully fetched.')
                    }, error => {
                        this.is_loading = false;
                        this.$log.error('Can not fetch the general settings.')
                    })
                }
            },
            updated_tax_filter(data){
                let taxonomies = []
               for (let index in data) {
                    taxonomies.push(data[index].value)
                }

                let taxonomy_objects = [];
                for(let tax  of this.taxonomy ){
                    for (let tax_id of taxonomies) {
                        tax_id = parseInt(tax_id);
                        let id = parseInt(tax.value);
                        if(id === tax_id){
                            taxonomy_objects.push(tax);
                        }
                    }

                }
                this.post_format.taxonomy_filter = taxonomy_objects;
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

    .block {
        display: block;
    }
</style>
