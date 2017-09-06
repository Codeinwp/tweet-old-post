<template>
    <button class="btn" :id="serviceId" :class="serviceClass" v-if="!authorized" @click="isAuthorized()" >Sign In w. {{ serviceName }}</button>
    <button class="btn" :id="serviceId" :class="serviceClass" v-else @click="isAuthorized()" >Sign Out from {{ serviceName }}</button>
</template>

<script>
    import vueVariables from './../variables.js';
    module.exports = {
        name: 'sign-in-btn',
        props: [ 'serviceName' ],
        data: function() {
            return {
                sharedState: vueVariables.page.state
            }
        },
        methods: {
            isAuthorized: function() {
                vueVariables.page.updateService( this.serviceName, !this.authorized )
            }
        },
        computed: {
            authorized: function() {
                return this.sharedState.authorizedService[this.serviceName.toLowerCase()]
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
    }
</script>