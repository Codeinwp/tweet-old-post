<template>
    <div class="tile tile-centered">
        <div class="tile-icon">
            <div class="icon_box" :class="service">
                <img class="service_account_image" :src="img" v-if="img" />
                <i class="fa" :class="icon" aria-hidden="true" v-else></i>
            </div>
        </div>
        <div class="tile-content">
            <div class="tile-title">{{ user }}</div>
            <div class="tile-subtitle text-gray">{{ service_info }}</div>
        </div>
        <div class="tile-action">
            <div class="dropdown dropdown-right">
                <a href="#" class="btn btn-link btn-danger" tabindex="0" @click.prevent="removeActiveAccount( account_id )">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    function capitalizeFirstLetter( string ) {
        return string.charAt(0).toUpperCase().concat( string.slice(1) );
    }

    module.exports = {
        name: 'service-user-tile',
        props: [ 'account_data', 'account_id' ],
        computed: {
            service: function() {
                var icon_class = this.account_data.service;
                if( this.img != '' ) {
                    icon_class = icon_class.concat( ' ' ).concat( 'has_image' )
                }
                return icon_class;
            },
            icon: function() {
                var service_icon = ('fa-');
                if( this.account_data.service === 'facebook' ) service_icon = service_icon.concat( 'facebook-official' );
                if( this.account_data.service === 'twitter' ) service_icon = service_icon.concat( 'twitter' );
                if( this.account_data.service === 'linkedin' ) service_icon = service_icon.concat( 'linkedin' );
                if( this.account_data.service === 'tumblr' ) service_icon = service_icon.concat( 'tumblr' );
                return service_icon;
            },
            img: function() {
                var img = '';
                if( this.account_data.img !== '' && this.account_data.img !== undefined ) {
                    img = this.account_data.img;
                }
                return img;
            },
            user: function() {
                return this.account_data.user;
            },
            service_info: function() {
                var service_info = this.account_data.account.concat( " at: " ).concat( this.account_data.created );
                return service_info;
            }
        },
        methods: {
            removeActiveAccount( id ) {
                this.$store.dispatch( 'updateActiveAccounts', { action: 'remove', account_id: id, current_active: this.$store.state.activeAccounts } );
            }
        }
    }
</script>

<style scoped>
    #rop_core .btn.btn-link.btn-danger {
        color: #d50000;
    }
    #rop_core .btn.btn-link.btn-danger:hover {
        color: #b71c1c;
    }

    .has_image {
        border-radius: 50%;
    }

    .service_account_image {
        width: 150%;
        border-radius: 50%;
        margin-left: -25%;
        margin-top: -25%;
    }

    .icon_box {
        width: 45px;
        height: 45px;
        padding: 7px;
        text-align: center;
        background-color: #333333;
        color: #efefef;
    }

    .icon_box > .fa {
        width: 30px;
        height: 30px;
        font-size: 30px;
    }

    .facebook {
        background-color: #3b5998;
    }

    .twitter {
        background-color: #55acee;
    }

    .linkedin {
        background-color: #007bb5;
    }

    .tumblr {
        background-color: #32506d;
    }

</style>