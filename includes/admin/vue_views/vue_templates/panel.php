<script type="text/x-template" id="panel-template">
    <div>
        <div class="panel title-panel" style="margin-bottom: 40px; padding-bottom: 20px;">
            <div class="panel-header">
                <img src="<?php echo ROP_LITE_URL . 'assets/img/logo_rop.png' ?>" style="float: left; margin-right: 10px;" />
                <h1 class="d-inline-block">Revive Old Posts</h1><span class="powered"> by <a href="https://themeisle.com" target="_blank"><b>ThemeIsle</b></a></span>
            </div>
        </div>
        <div class="panel">
            <div class="panel-nav" style="padding: 8px;">
                <ul class="tab">
                    <li class="tab-item" v-for="tab in tabs" :class="{ active: tab.isActive }"><a href="#" @click="switchTab( tab.slug )">{{ tab.name }}</a></li>
                    <li class="tab-item tab-action">
                        <div class="form-group">
                            <label class="form-switch">
                                <input type="checkbox" />
                                <i class="form-icon"></i> Beta User
                            </label>
                            <label class="form-switch">
                                <input type="checkbox" />
                                <i class="form-icon"></i> Remote Check
                            </label>
                        </div>
                    </li>
                </ul>
            </div>

            <component :is="sharedState.view"></component>
        </div>
    </div>
</script>

<script type="text/x-template" id="account-template">
    <div class="tab-view">
        <div class="panel-body">
            <h3>Accounts</h3>
            <p>This is a <b>Vue.js</b> component.</p>
            <sign-in-btn service-name="Twitter" ></sign-in-btn>
            <sign-in-btn service-name="Facebook" ></sign-in-btn>
        </div>
        <div class="panel-footer">
            <button class="btn btn-primary btn-block">Cool!</button>
        </div>
    </div>
</script>

<script type="text/x-template" id="logs-template">
    <div class="container">
        <h3>Logs</h3>
        <div class="columns">
            <div class="column col-12">
                <pre class="code" data-lang="Vue.js">
                    <code>{{ logs }}</code>
                </pre>
            </div>
        </div>
    </div>
</script>

<script type="text/x-template" id="sign-in-button-template">
    <button class="btn" :id="serviceId" :class="serviceClass" v-if="!authorized" @click="isAuthorized()" >Sign In w. {{ serviceName }}</button>
    <button class="btn" :id="serviceId" :class="serviceClass" v-else @click="isAuthorized()" >Sign Out from {{ serviceName }}</button>
</script>