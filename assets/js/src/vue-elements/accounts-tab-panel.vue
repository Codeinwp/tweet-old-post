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
                                <service-tile v-for="service in authenticated_services" :key="service.id" :service="service"></service-tile>
                            </div>
                        </div>
                    </div>
                    <div class="column col-sm-12 col-md-12 col-lg-6 text-left">
                        <h5>Active Accounts</h5>
                        <hr/>
                        <div class="column col-12" v-for="account in active_accounts">
                            <service-user-tile :account_data="account"></service-user-tile>
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
    import SignInBtn from './sign-in-btn.vue';
    import ServiceTile from './service-tile.vue';
    import ServiceUserTile from './service-user-tile.vue';

    import { mapState } from 'vuex'

    module.exports = {
        name: 'account-view',
        computed: {
            authenticated_services: function() {
                return this.$store.state.authenticatedServices;
            },
            active_accounts: function() {
                return this.$store.state.activeAccounts;
            }
        },
        components: {
            SignInBtn,
            ServiceTile,
            ServiceUserTile,
        }
    }
</script>