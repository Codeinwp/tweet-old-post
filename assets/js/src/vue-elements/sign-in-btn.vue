<template>
    <div class="sign-in-btn">
        <div class="input-group">
            <select class="form-select" v-model="selected_network">
                <option v-for="( service, network ) in services" v-bind:value="network" :disabled="!service.active">{{ service.name }}</option>
            </select>
            <button class="btn input-group-btn" :class="serviceClass" @click="requestAuthorization()" >
                <i class="fa" :class="serviceIcon" aria-hidden="true"></i> Sign In
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
                            <input class="form-input" type="text" :id="field.id" :placeholder="field.name" />
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
                }
            }
        },
        methods: {
            requestAuthorization: function() {
                if( this.$store.state.availableServices[this.selected_network].two_step_sign_in ) {
                    this.modal.serviceName = this.$store.state.availableServices[this.selected_network].name;
                    this.modal.data = this.$store.state.availableServices[this.selected_network].credentials;
                    this.openModal()
                }
            },
            openModal: function() {
                this.modal.isOpen = true;
            },
            closeModal: function() {
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
                }
            },
            serviceIcon: function() {
                return {
                    'fa-twitter': this.selected_network === 'twitter',
                    'fa-facebook-official': this.selected_network === 'facebook',
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