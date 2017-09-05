<script type="text/x-template" id="panel-template">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title h1">Revive Old Posts</div>
        </div>
        <div class="panel-nav">
            <!-- navigation components: tabs, breadcrumbs or pagination -->
        </div>
        <div class="panel-body">
        <!-- contents -->
            <p>This is a <b>Vue.js</b> component.</p>
            <sign-in-btn service-name="Twitter" ></sign-in-btn>
            <sign-in-btn service-name="Facebook" ></sign-in-btn>
        </div>
        <div class="panel-footer">
            <!-- buttons or inputs -->
            <button class="btn btn-primary btn-block">Cool!</button>
        </div>
    </div>
</script>

<script type="text/x-template" id="sign-in-button-template">
    <button class="btn" :id="serviceId" :class="serviceClass" v-if="!authorized" @click="isAuthorized()" >Sign In w. {{ serviceName }}</button>
    <button class="btn" :id="serviceId" :class="serviceClass" v-else @click="isAuthorized()" >Sign Out from {{ serviceName }}</button>
</script>