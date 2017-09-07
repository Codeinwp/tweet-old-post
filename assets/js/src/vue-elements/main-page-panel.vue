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
                    <li class="tab-item" v-for="tab in tabs" :class="{ active: tab.isActive }"><a href="#" @click="switchTab( tab.slug )">{{ tab.name }}</a></li>
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

            <component :is="sharedState.view" :model="model"></component>
        </div>
    </div>
</template>

<script>
    import AccountsTab from './accounts-tab-panel.vue';
    import LogsTab from './logs-tab-panel.vue';

    module.exports = {
        name: 'main-page-panel',
        props: [ 'model' ],
        data: function() {
            return {
                tabs: this.model.tabs,
                sharedState: this.model.page.state
            }
        },
        methods: {
            switchTab: function( slug ) {
                for( var tab in this.tabs ) {
                    this.tabs[tab].isActive = false;
                    if( this.tabs[tab].slug === slug ) {
                        this.tabs[tab].isActive = true;
                        this.sharedState.view = slug;
                    }
                }
            }
        },
        components: {
            'accounts': AccountsTab,
            settings: {
                name: 'settings-view',
                template: '<span>This is not yet ready</span>',
            },
            post: {
                name: 'post-view',
                template: '<span>This is not yet ready</span>',
            },
            schedule: {
                name: 'schedule-view',
                template: '<span>This is not yet ready</span>',
            },
            'logs': LogsTab
        }
    };
</script>