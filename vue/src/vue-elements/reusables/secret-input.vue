<template>
    <div class="input-group" v-if="secret">
        <input class="form-input" :type="input_type" :id="id" :value="value" :readonly="readonly">
        <button class="btn input-group-btn" @mouseenter="showHideSecret()" @mouseleave="showHideSecret()"><i class="fa fa-fw" :class="visibileClass"></i></button>
    </div>
    <input class="form-input" type="text" :id="id" :value="value" :readonly="readonly" v-else>
</template>
<script>
    module.exports = {
    	name: 'secret-input',
    	props: {
    		id: {
    			default: ''
    		},
    		secret: {
    			type: Boolean,
    			default: true
    		},
    		value: {
    			default: ''
    		},
    		readonly: {
    			type: Boolean,
    			default: true
    		}
    	},
    	data: function () {
    		return {
    			visible: false
    		}
    	},
    	computed: {
    		input_type: function () {
    			if ( this.visible ) {
    				return 'text'
    			}
    			return 'password'
    		},
    		visibileClass: function () {
    			return {
    				'fa-eye': this.visible === true,
    				'fa-eye-slash': this.visible === false
    			}
    		}
    	},
    	methods: {
    		showHideSecret () {
    			this.visible = !this.visible
    		}
    	}
    }
</script>