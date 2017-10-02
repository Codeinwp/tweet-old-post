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
        data: function() {
            console.log( this.$store.state );
            return {
                authenticated_services: {
                    'serviceIDFacebook': {
                        id: 'serviceID',
                        service: 'facebook',
                        credentials: {
                            app_id: {
                                name: 'App ID',
                                value: 'dasdassdaUYASGDUY!@&',
                                private: false,
                            },
                            secret: {
                                name: 'Secret',
                                value: 'sdaUYASGDUY!@&WHDQ',
                                private: true,
                            }
                        },
                        available_accounts: [
                            {
                                id: 'account_id_1',
                                name: 'Page one',
                                img: ''
                            },
                            {
                                id: 'account_id_2',
                                name: 'Page two',
                                img: 'http://www.xsjjys.com/data/out/96/WHDQ-512397052.jpg'
                            },
                            {
                                id: 'account_id_3',
                                name: 'Page three',
                                img: 'https://organicthemes.com/demo/profile/files/2012/12/profile_img.png'
                            }
                        ]
                    },
                    'serviceIDTwitter': {
                        id: 'serviceID',
                        service: 'twitter',
                        available_accounts: [
                            {
                                id: 'account_id_1',
                                name: '@username',
                                img: ''
                            },
                        ]
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