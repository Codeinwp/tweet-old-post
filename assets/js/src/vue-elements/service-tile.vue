<template>
    <div class="service-tile">
        <div class="input-group">
            <button class="btn input-group-btn" @click="" >
                <i class="fa fa-fw fa-trash" aria-hidden="true"></i>
            </button>
            <button class="btn input-group-btn" @click="" >
                <i class="fa fa-fw fa-info-circle" aria-hidden="true"></i>
            </button>
            <span class="input-group-addon">facebook.com/</span>
            <service-autocomplete></service-autocomplete>
            <!--<select class="form-select">-->
                <!--<option default>Select an account to activate</option>-->
                <!--&lt;!&ndash;<option v-for="( service, network ) in services" v-bind:value="network">{{ service.name }}</option>&ndash;&gt;-->
            <!--</select>-->
            <button class="btn input-group-btn" >
                <i class="fa fa-plus" aria-hidden="true"></i> Activate
            </button>
        </div>
    </div>
</template>

<script>
    import ServiceAutocomplete from './service-autocomplete.vue';

    function capitalizeFirstLetter( string ) {
        return string.charAt(0).toUpperCase().concat( string.slice(1) );
    }

    module.exports = {
        name: 'service-tile',
        data: function() {
            return {

            }
        },
        components: {
            ServiceAutocomplete,
        }
    }
</script>

<style scoped>
    .facebook {
        background-color: #3b5998;
    }

    .twitter {
        background-color: #55acee;
    }

</style>