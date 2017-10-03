<template>
    <div class="service-tile">
        <label class="show-md hide-xl"><b>{{service_url}}/</b></label>
        <div class="input-group">
            <button class="btn input-group-btn btn-danger" @click="" >
                <i class="fa fa-fw fa-trash" aria-hidden="true"></i>
            </button>
            <button class="btn input-group-btn btn-info" @click="toggleCredentials()" v-if="service.credentials" >
                <i class="fa fa-fw fa-info-circle" aria-hidden="true"></i>
            </button>
            <span class="input-group-addon hide-md" style="min-width: 115px; text-align: right;">{{service_url}}/</span>
            <service-autocomplete :accounts="service.available_accounts" :to_be_activated="to_be_activated"></service-autocomplete>
            <button class="btn input-group-btn" :class="serviceClass" @click="activateSelected( service.id )">
                <i class="fa fa-fw fa-plus" aria-hidden="true"></i> <span class="hide-md">Activate</span>
            </button>
        </div>
        <div class="card centered" :class="credentialsDisplayClass" v-if="service.credentials">
            <div class="card-header">
                <div class="card-title h5">{{serviceName}}</div>
                <div class="card-subtitle text-gray">{{service.id}}</div>
            </div>
            <div class="card-body">
                <div class="form-horizontal">
                    <div class="form-group" v-for="( credential, index ) in service.credentials">
                        <div class="col-3">
                            <label class="form-label" :for="credentialID(index)">{{credential.name}}:</label>
                        </div>
                        <div class="col-9">
                            <secret-input :id="credentialID(index)" :value="credential.value" :secret="credential.private" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="divider clearfix"></div>
    </div>
</template>

<script>
    import ServiceAutocomplete from './service-autocomplete.vue';
    import SecretInput from './reusables/secret-input.vue';

    function capitalizeFirstLetter( string ) {
        return string.charAt(0).toUpperCase().concat( string.slice(1) );
    }

    module.exports = {
        name: 'service-tile',
        props: {
            service: {
                type: Object,
                required: true
            },
        },
        data: function() {
            return {
                show_credentials: false,
                to_be_activated: []
            }
        },
        computed: {
            service_url: function() {
                if( this.service.service === 'facebook' ) {
                    return 'facebook.com';
                }
                if( this.service.service === 'twitter' ) {
                    return 'twitter.com';
                }

                return 'service.url';
            },
            serviceName: function() {
              return capitalizeFirstLetter( this.service.service );
            },
            serviceClass: function() {
                return {
                    'btn-twitter': this.service.service === 'twitter',
                    'btn-facebook': this.service.service === 'facebook',
                }
            },
            credentialsDisplayClass: function() {
                return {
                    'd-block': this.show_credentials === true,
                    'd-none': this.show_credentials === false,
                }
            }
        },
        methods: {
            credentialID( index ) {
                return 'service-' + index + '-field';
            },
            toggleCredentials() {
                this.show_credentials = ! this.show_credentials;
            },
            activateSelected( service_id ) {
                this.$store.dispatch( 'updateActiveAccounts', { action: 'update', service_id: service_id, service: this.service.service, to_be_activated: this.to_be_activated, current_active: this.$store.state.activeAccounts } );
            }
        },
        components: {
            ServiceAutocomplete,
            SecretInput
        }
    }
</script>

<style scoped>

    #rop_core .btn.btn-danger {
        background-color: #d50000;
        color: #efefef;
        border-color: #b71c1c;
    }

    #rop_core .btn.btn-danger:hover, #rop_core {
        background-color: #efefef;
        color: #d50000;
        border-color: #b71c1c;
    }

    #rop_core .btn.btn-info {
        background-color: #2196f3;
        color: #efefef;
        border-color: #1565c0;
    }

    #rop_core .btn.btn-info:hover, #rop_core {
        background-color: #efefef;
        color: #2196f3;
        border-color: #1565c0;
    }

</style>