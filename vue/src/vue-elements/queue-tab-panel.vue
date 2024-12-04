<template>
  <div class="tab-view rop-queue-tab-container">
    <div
      class="panel-body"
      :class="'rop-tab-state-'+is_loading"
    >
      <div
        v-if="! start_status"
        class="columns"
      >
        <div class="column col-12 text-center empty-container">
          <div class="empty-icon">
            <i class="fa fa-3x fa-info-circle" />
          </div>
          <p class="empty-title h5">
            {{ labels.sharing_not_started }}
          </p>
          <p class="empty-subtitle">
            {{ labels.sharing_not_started_desc }}
          </p>
        </div>
      </div>

      <div v-else-if="start_status && queueCount > 0 ">
        <div
          v-if="! is_business_and_higher"
          class="columns py-2"
        >
          <div class="column text-center">
            <p class="upsell">
              <i class="fa fa-lock" /> <span v-html="labels.business_or_higher_only" />
            </p>
          </div>
        </div>

        <!-- When sharing is started but we  have the business plan. -->
        <div class="d-inline-block mt-2 column col-8">
          <p class="text-gray info-paragraph">
            <i class="fa fa-info-circle" /> {{ labels.queue_desc }}
          </p>
        </div>
        <div
          v-if="start_status"
          class="d-inline-block mt-2 column col-4 float-right text-right"
        >
          <button
            class="btn btn-secondary"
            @click="refreshQueue(true)"
          >
            <i
              v-if="!is_loading"
              class="fa fa-refresh"
            />
            <i
              v-else
              class="fa fa-spinner fa-spin"
            />
            {{ labels.refresh_btn }}
          </button>
        </div>
      </div>
      <div
        v-else-if="start_status && queueCount === 0"
        class="empty"
      >
        <div class="empty-icon">
          <i class="fa fa-3x fa-info-circle" />
        </div>
        <p class="empty-title h5">
          {{ labels.no_posts }}
        </p>
        <p
          class="empty-subtitle"
          v-html="labels.no_posts_desc"
        />
      </div>
      <div
        v-if="start_status && queueCount > 0"
        class="columns"
      >
        <div
          v-for=" (data, index) in queue "
          :key="index"
          class="column col-12 text-left"
        >
          <queue-card
            :id="index"
            :card_data="data.post_data"
            :enabled="is_business_and_higher"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
	import QueueCard from './reusables/queue-card.vue'

	export default {
		name: 'QueueView',
		components: {
			QueueCard
		},
		data: function () {
			return {
				is_loading: false,
				labels: this.$store.state.labels.queue,
				upsell_link: ropApiSettings.upsell_link,
			}
		},
		computed: {
			queueCount: function () {
				return Object.keys(this.$store.state.queue).length
			},
			queue: function () {
				return this.$store.state.queue
			},
			start_status: function () {
				return this.$store.state.cron_status.current_status
			},
			is_business_and_higher: function () {
				return (this.$store.state.license > 1 && this.$store.state.license !== 7)
			},
		},
		watch: {
			start_status: function (new_val) {
				this.refreshQueue();
			}
		},
		mounted: function () {
			if (this.start_status) {
				this.refreshQueue( false );
			}
		},
		methods: {
			refreshQueue: function (force) {
				if (this.is_loading) {
					this.$log.warn('Request in progress...Bail');
					return;
				}
				this.$store.state.queue = [];
				this.is_loading = true;
				this.$store.dispatch('fetchAJAXPromise', {req: 'get_queue', data: {force: force}}).then(response => {
					this.is_loading = false;
					this.$store.dispatch('fetchAJAX', {req: 'manage_cron'});
				}, error => {
					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			}
		}
	}
</script>
