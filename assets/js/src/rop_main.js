require('./vue-elements/test.js');

function logMessage( log, message ) {
    return log.concat( message + '\n' );
}

window.onload = function () {
    var page = {
        debug: true,
        logs: 'Here starts the log \n\n',
        state: {
            authorizedService: {
                twitter: false,
                facebook: false
            },
            view: 'accounts'
        },
        updateService: function( serviceName, serviceStatus ) {
            if (this.debug) console.log('updateService triggered by', serviceName)
            if (this.debug) this.logs = logMessage( this.logs, 'updateService triggered by ' + serviceName );
            this.state.authorizedService[serviceName.toLowerCase()] = serviceStatus
        }
    }

    var tabs = [
        {
            name: 'Accounts',
            slug: 'accounts',
            isActive: true
        },
        {
            name: 'General Settings',
            slug: 'settings',
            isActive: false
        },
        {
            name: 'Post Format',
            slug: 'post',
            isActive: false
        },
        {
            name: 'Custom Schedule',
            slug: 'schedule',
            isActive: false
        },
        {
            name: 'Logs',
            slug: 'logs',
            isActive: false
        },
    ]

    // register
    Vue.component('my-component', {
        template: '#panel-template',
        data: function() {
            return {
                tabs: tabs,
                sharedState: page.state
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
                        logs: page.logs
                    }
                }
            },
        }
    })

    Vue.component('sign-in-btn', {
        props: [ 'serviceName' ],
        template: '#sign-in-button-template',
        data: function() {
            return {
                sharedState: page.state
            }
        },
        methods: {
          isAuthorized: function() {
              page.updateService( this.serviceName, !this.authorized )
          }
        },
        computed: {
            authorized: function() {
              return page.state.authorizedService[this.serviceName.toLowerCase()]
            },
            serviceClass: function () {
                return {
                    'btn-twitter': this.serviceName === 'Twitter',
                    'btn-facebook': this.serviceName === 'Facebook',
                }
            },
            serviceId: function() {
                return 'service-' + this.serviceName.toLowerCase()
            }
        }
    })
    // create a root instance
    new Vue({
        el: '#rop_core'
    })
};