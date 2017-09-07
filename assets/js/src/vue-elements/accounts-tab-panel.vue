<template>
    <div class="tab-view">
        <div class="panel-body">
            <h3>Accounts</h3>
            <p>This is a <b>Vue.js</b> component.</p>
            <sign-in-btn service-name="Twitter" :model="model" ></sign-in-btn>
            <sign-in-btn service-name="Facebook" :model="model" ></sign-in-btn>
        </div>
        <div class="panel-footer">
            <button class="btn btn-primary btn-block">Cool!</button>
        </div>
    </div>
</template>

<script>
    import SignInBtn from './sign-in-btn.vue';

    module.exports = {
        name: 'account-view',
        props: [ 'model' ],
        components: {
            SignInBtn
        }
    }
</script>