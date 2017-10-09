<template>
    <div class="tab-view">
        <div class="panel-body">
            <h3>Accounts</h3>
            <p>This is a <b>Vue.js</b> component.</p>
            <div class="container">
                <div class="columns">
                    <div class="column col-sm-12 col-md-12 col-lg-6">
                        <div class="columns">
                            <div class="column col-sm-12 col-md-12 col-xl-6 col-8 text-right">
                                <b>New Service</b><br/>
                                <i>Select a service and sign in with an account for that service.</i>
                            </div>
                            <div class="column col-sm-12 col-md-12 col-xl-6 col-4 text-left">
                                <sign-in-btn></sign-in-btn>
                            </div>
                        </div>
                        <div class="columns">
                            <div class="column col-sm-12 col-md-12 col-lg-12 text-left">
                                <hr/>
                                <h5>Authenticated Services</h5>
                                <div class="empty" v-if="authenticated_services.length == 0">
                                    <div class="empty-icon">
                                        <i class="fa fa-3x fa-cloud"></i>
                                    </div>
                                    <p class="empty-title h5">No authenticated service!</p>
                                    <p class="empty-subtitle">Add one from the <b>"New Service"</b> section.</p>
                                </div>
                                <service-tile v-for="service in authenticated_services" :key="service.id" :service="service"></service-tile>
                            </div>
                        </div>
                    </div>
                    <div class="column col-sm-12 col-md-12 col-lg-6 text-left">
                        <hr style="margin-top: 45px" />
                        <h5>Active Accounts</h5>
                        <div class="empty" v-if="active_accounts.length == 0">
                            <div class="empty-icon">
                                <i class="fa fa-3x fa-user-circle-o"></i>
                            </div>
                            <p class="empty-title h5">No active accounts!</p>
                            <p class="empty-subtitle">Add one from the <b>"Authenticated Services"</b> section.</p>
                        </div>
                        <div v-for="( account, id ) in active_accounts">
                            <service-user-tile :account_data="account" :account_id="id"></service-user-tile>
                            <div class="divider"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="columns">
                <div class="column col-12">
                    <h4><i class="fa fa-info-circle"></i> Info</h4>
                    <p><i>Authenticate a new service (eg. Facebook, Twitter etc. ), select the accounts you want to add from that service and <b>activate</b> them. Only the accounts displayed in the <b>"Active accounts"</b> section will be used.</i></p>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button class="btn btn-primary">Save</button>
        </div>
    </div>
</template>

<script>
    import SignInBtn from './sign-in-btn.vue'
    import ServiceTile from './service-tile.vue'
    import ServiceUserTile from './service-user-tile.vue'

    module.exports = {
        name: 'account-view',
        computed: {
            authenticated_services: function () {
                return this.$store.state.authenticatedServices
            },
            active_accounts: function () {
                return this.$store.state.activeAccounts
            }
        },
        components: {
            SignInBtn,
            ServiceTile,
            ServiceUserTile
        }
    }
</script>