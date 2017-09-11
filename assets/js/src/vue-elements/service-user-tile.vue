<template>
    <div class="tile tile-centered">
        <div class="tile-icon">
            <div class="icon_box" :class="service">
                <i class="fa" :class="icon" aria-hidden="true"></i>
            </div>
        </div>
        <div class="tile-content">
            <div class="tile-title">{{ user }}</div>
            <div class="tile-subtitle text-gray">{{ service_info }}</div>
        </div>
        <div class="tile-action">
            <div class="dropdown dropdown-right">
                <a href="#" class="btn btn-link dropdown-toggle" tabindex="0">
                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                </a>
                <!-- menu component -->
                <ul class="menu">
                    <li class="menu-item">
                        <a @click=""><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
                    </li>
                    <li class="menu-item">
                        <a @click=""><i class="fa fa-trash" aria-hidden="true"></i> Remove</a>
                    </li>
                </ul>
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
        props: [ 'account_data', 'model' ],
        data: function() {
            var service_icon = ('fa-');
            if( this.account_data.service === 'facebook' ) service_icon = service_icon.concat( 'facebook-official' )
            if( this.account_data.service === 'twitter' ) service_icon = service_icon.concat( 'twitter' )
            var service_info = this.account_data.account.concat( " at: " ).concat( this.account_data.created )
            return {
                service: this.account_data.service,
                icon: service_icon,
                user: this.account_data.user,
                service_info: service_info
            }
        },
    }
</script>

<style scoped>
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

</style>