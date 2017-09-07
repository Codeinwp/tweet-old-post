import vueVariables from './variables.js';
import MainPagePanel from './vue-elements/main-page-panel.vue';

window.onload = function () {

    // create a root instance
    new Vue({
        el: '#rop_core',
        data: {
            model: {
                page: vueVariables.page,
                tabs: vueVariables.tabs
            }
        },
        components: {
            MainPagePanel,
        },
        created: function() {
            console.log( this.$options.components );
        }
    });
};