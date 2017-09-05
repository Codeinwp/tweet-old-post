window.onload = function () {
    var page = {
        debug: true,
        state: {
            authorizedService: {
                twitter: false,
                facebook: false
            }
        },
        updateService: function( serviceName, serviceStatus ) {
            if (this.debug) console.log('updateService triggered by', serviceName)
            this.state.authorizedService[serviceName.toLowerCase()] = serviceStatus
        }
    }

    // register
    Vue.component('my-component', {
        template: '#panel-template',
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