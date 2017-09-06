import vueVariables from './variables.js';
import SignInBtn from './vue-elements/SignInBtn.vue';

window.onload = function () {
    // register
    var myComponent = {
        template: '#panel-template',
        data: function() {
            return {
                tabs: vueVariables.tabs,
                sharedState: vueVariables.page.state
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
            accounts: {
                name: 'account-view',
                template: '#account-template',
                components: {
                    'sign-in-btn': SignInBtn
                }
            },
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
            logs: {
                name: 'logs-view',
                template: '#logs-template',
                data: function() {
                    return {
                        logs: vueVariables.page.logs
                    }
                }
            }
        }
    };

    // Vue.component( 'sign-in-btn', SignInBtn );

    // create a root instance
    new Vue({
        el: '#rop_core',
        data: {
            page: vueVariables.page,
            tabs: vueVariables.tabs
        },
        components: {
            'my-component': myComponent,
        },
        created: function() {
            console.log( this.$options.components );
        }
    });
};