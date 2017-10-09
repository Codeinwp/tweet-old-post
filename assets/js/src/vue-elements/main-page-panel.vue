<template>
    <div>
        <div class="panel title-panel" style="margin-bottom: 40px; padding-bottom: 20px;">
            <div class="panel-header">
                <!--<img src="./../../../img/logo_rop.png" style="float: left; margin-right: 10px;" />-->
                <h1 class="d-inline-block">Revive Old Posts</h1><span class="powered"> by <a href="https://themeisle.com" target="_blank"><b>ThemeIsle</b></a></span>
            </div>
        </div>
        <div class="panel">
            <div class="panel-nav" style="padding: 8px;">
                <ul class="tab">
                    <li class="tab-item" v-for="tab in displayTabs" :class="{ active: tab.isActive }"><a href="#" @click="switchTab( tab.slug )">{{ tab.name }}</a></li>
                    <li class="tab-item tab-action">
                        <div class="form-group">
                            <label class="form-switch">
                                <input type="checkbox" />
                                <i class="form-icon"></i> Beta User
                            </label>
                            <label class="form-switch">
                                <input type="checkbox" />
                                <i class="form-icon"></i> Remote Check
                            </label>
                        </div>
                    </li>
                </ul>
            </div>

            <component :is="page.view"></component>
        </div>
    </div>
</template>

<script>
    import AccountsTab from './accounts-tab-panel.vue'
    import LogsTab from './logs-tab-panel.vue'

    import { mapState } from 'vuex'

    module.exports = {
        name: 'main-page-panel',
        computed: mapState([ 'displayTabs', 'page' ]),
        created () {
        },
        methods: {
            switchTab (slug) {
                this.$store.commit('setTabView', slug)
            }
        },
        components: {
            'accounts': AccountsTab,
            settings: {
                name: 'settings-view',
                template: '<span>This is not yet ready</span>'
            },
            post: {
                name: 'post-view',
                template: '<span>This is not yet ready</span>'
            },
            schedule: {
                name: 'schedule-view',
                template: '<span>This is not yet ready</span>'
            },
            'logs': LogsTab
        }
    }
</script>