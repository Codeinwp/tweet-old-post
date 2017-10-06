<template>
    <div class="sign-in-btn">
        <div class="input-group">
            <select class="form-select" v-model="selected_network">
                <option v-for="( service, network ) in services" v-bind:value="network" :disabled="!service.active">{{ service.name }}</option>
            </select>
            <button class="btn input-group-btn" :class="serviceClass" @click="requestAuthorization()" >
                <i class="fa fa-fw" :class="serviceIcon" aria-hidden="true"></i> Sign In
            </button>
        </div>
        <div class="modal" :class="modalActiveClass">
            <div class="modal-overlay"></div>
            <div class="modal-container">
                <div class="modal-header">
                    <button class="btn btn-clear float-right" @click="closeModal()"></button>
                    <div class="modal-title h5">{{ modal.serviceName }} Service Credentials</div>
                </div>
                <div class="modal-body">
                    <div class="content">
                        <div class="form-group" v-for="( field, id ) in modal.data">
                            <label class="form-label" :for="field.id">{{ field.name }}</label>
                            <input class="form-input" type="text" :id="field.id" v-model="field.value" :placeholder="field.name" />
                            <i>{{ field.description }}</i>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" @click="closeModal()">Sign in</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'

    module.exports = {
        name: 'sign-in-btn',
        created() {
        },
        data: function() {
            return {
                modal: {
                    isOpen: false,
                    serviceName: '',
                    data: {}
                },
                activePopup: '',
            }
        },
        methods: {
            requestAuthorization: function() {
                if( this.$store.state.availableServices[this.selected_network].two_step_sign_in ) {
                    this.modal.serviceName = this.$store.state.availableServices[this.selected_network].name;
                    this.modal.data = this.$store.state.availableServices[this.selected_network].credentials;
                    this.openModal()
                } else {
                    this.activePopup = this.selected_network
                    let w = 560;
                    let h = 340;
                    let y = window.top.outerHeight / 2 + window.top.screenY - ( w / 2);
                    let x = window.top.outerWidth / 2 + window.top.screenX - ( h / 2);
                    window.open( '', this.activePopup,'width=' + w + ', height=' + h + ', toolbar=0, menubar=0, location=0, top=' + y + ', left=' + x );
                    this.getUrlAndGo( [] );
                }
            },
            openPopup( url ) {
                console.log( 'Trying to open popup for url:', url );
                let newWindow = window.open( url, this.activePopup );
                if ( window.focus ) { newWindow.focus(); }
                let instance = this;
                let pollTimer = window.setInterval( function() {
                    if ( newWindow.closed !== false ) {
                        window.clearInterval( pollTimer );
                        instance.requestAuthentication();
                    }
                }, 200);
            },
            getUrlAndGo( credentials ) {
                console.log( 'Credentials recieved:', credentials );
                this.$store.dispatch( 'getServiceSignInUrl', { service: this.selected_network, credentials: credentials } ).then(response => {
                    console.log("Got some data, now lets show something in this component", response);
                    this.openPopup( response.url );
                }, error => {
                    console.error("Got nothing from server. Prompt user to check internet connection and try again", error);
                })
            },
            requestAuthentication() {
                this.$store.dispatch( 'authenticateService', { service: this.selected_network } );
            },
            openModal: function() {
                this.modal.isOpen = true;
            },
            closeModal: function() {
                let credentials = {};
                for( const index of Object.keys( this.modal.data ) ) {
                    credentials[index] = '';
                    if( 'value' in this.modal.data[index] ) {
                        credentials[index] = this.modal.data[index]['value'];
                    }
                }
                //console.log( 'credentials: ', credentials );

                this.activePopup = this.selected_network
                let w = 560;
                let h = 340;
                let y = window.top.outerHeight / 2 + window.top.screenY - ( w / 2);
                let x = window.top.outerWidth / 2 + window.top.screenX - ( h / 2);
                window.open( '', this.activePopup,'width=' + w + ', height=' + h + ', toolbar=0, menubar=0, location=0, top=' + y + ', left=' + x );
                this.getUrlAndGo( credentials );

                this.modal.isOpen = false;
            }
        },
        computed: {
            selected_network: {
                get: function() {
                    var default_network = this.modal.serviceName;
                    if( Object.keys( this.services )[0] && default_network === '' ) {
                        default_network = Object.keys( this.services )[0];
                    }
                    return default_network.toLowerCase()
                },
                set: function( new_network ) {
                    this.modal.serviceName = new_network;
                }
            },
            services: function() {
                return this.$store.state.availableServices
            },
            modalActiveClass: function() {
                return {
                    'active': this.modal.isOpen === true
                }
            },
            serviceClass: function () {
                return {
                    'btn-twitter': this.selected_network === 'twitter',
                    'btn-facebook': this.selected_network === 'facebook',
                    'btn-linkedin': this.selected_network === 'linkedin',
                    'btn-tumblr': this.selected_network === 'tumblr',
                }
            },
            serviceIcon: function() {
                return {
                    'fa-twitter': this.selected_network === 'twitter',
                    'fa-facebook-official': this.selected_network === 'facebook',
                    'fa-linkedin': this.selected_network === 'linkedin',
                    'fa-tumblr': this.selected_network === 'tumblr',
                }
            },
            serviceId: function() {
                return 'service-' + this.modal.serviceName.toLowerCase()
            }
        }
    }
</script>

<style scoped>
    #rop_core .sign-in-btn > .modal {
        position: absolute;
        top: 20px;
    }

    #rop_core .sign-in-btn > .modal > .modal-container {
        width: 100%;
    }

</style>