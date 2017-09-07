<template>
    <button class="btn" :id="serviceId" :class="serviceClass" v-if="!authorized" @click="isAuthorized()" >Sign In w. {{ serviceName }}</button>
    <button class="btn" :id="serviceId" :class="serviceClass" v-else @click="isAuthorized()" >Sign Out from {{ serviceName }}</button>
</template>

<script>
    module.exports = {
        name: 'sign-in-btn',
        props: [ 'serviceName', 'model' ],
        data: function() {
            return {
                sharedState: this.model.page.state
            }
        },
        methods: {
            isAuthorized: function() {
                this.model.page.updateService( this.serviceName, !this.authorized )
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