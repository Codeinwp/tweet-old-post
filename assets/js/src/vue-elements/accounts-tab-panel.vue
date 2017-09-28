<template>
    <div class="tab-view">
        <div class="panel-body">
            <h3>Accounts</h3>
            <p>This is a <b>Vue.js</b> component.</p>
            <div class="container grid-md float-left">
                <div class="columns">
                    <div class="column col-6 text-right">
                        <b>New Service</b><br/>
                        <i>Select a service and sign in with an account for that service.</i>
                    </div>
                    <div class="column col-6 text-left">
                        <sign-in-btn></sign-in-btn>
                    </div>
                </div>
                <hr/>
                <h5>Authenticated Services</h5>
                <service-tile v-for="service in authenticated_services" :key="service.id" :service="service"></service-tile>
            </div>
            <div class="container grid-sm float-left">
                <h5 style="margin-bottom: 22px;">Active Accounts</h5>
                <hr/>
                <div class="columns">
                    <div class="column col-12" v-for="account in active_accounts">
                        <service-user-tile :account_data="account"></service-user-tile>
                        <div class="divider"></div>
                    </div>
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
        data: function() {
            console.log( this.$store.state );
            return {
                authenticated_services: {
                    'serviceID': {
                        id: 'serviceID',
                        service: 'facebook',
                        available_accounts: {

                        }
                    }
                },
                active_accounts: [
                    {
                        service: 'facebook',
                        user: 'Company Page',
                        account: 'user@email.com',
                        created: '07/09/2017 15:16'
                    },
                    {
                        service: 'twitter',
                        user: 'John Doe',
                        account: '@unkownjoe',
                        created: '07/09/2017 15:16'
                    }
                ],
            }
        },
        components: {
            SignInBtn,
            ServiceTile,
            ServiceUserTile,
        }
    }
</script>