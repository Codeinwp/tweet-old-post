<template>
  <transition v-if="display_the_preloader">
    <div class="preloader-container">
      <div class="preloader-body text-center">
        <p
          class="empty-title h5"
          v-html="loading_message"
        />
        <loader-style
          :loading="loading"
          :color="color"
          :size="size"
          :margin="margin"
          :radius="radius"
        />
      </div>
    </div>
  </transition>
</template>

<script>

    import Loader_Style from './preload_three_dots.vue'

    export default {
        name: "VueSpinner",
        components: {
	        'loader-style': Loader_Style
        },
        props: {
            loading: {
                type: Boolean,
                default: true
            },
            color: {
                type: String,
                default: '#429bf4'
            },
            size: {
                type: String,
                default: '30px'
            },
            margin: {
                type: String,
                default: '2px'
            },
            radius: {
                type: String,
                default: '100%'
            },
            preloader_message: {
                type: String,
                default: 'Loading...'
            }
        },
        data () {
            return {
                display_the_preloader: false,
                loading_message: this.preloader_message
            }
        },
        mounted () {

        },
        methods: {
            show: function () {
                this.display_the_preloader = true
            },
            hide: function () {
                this.display_the_preloader = false
            }
        }
    }
</script>

<style scoped>
    .preloader-container {
        position: relative;
        z-index: 9998;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        margin: auto;
        width: 100%;
        height: 218px;
        background-color: rgba(255, 255, 255, 0.1);
        display: block;
    }

    .preloader-body {
        width: 350px;
        height: 80px;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
        text-align: center;
        display: block;
    }
</style>
