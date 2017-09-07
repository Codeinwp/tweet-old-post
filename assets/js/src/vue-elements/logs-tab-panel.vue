<template>
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
</template>

<script>
    module.exports = {
        name: 'logs-view',
        props: [ 'model' ],
        data: function() {
            return {
                logs: this.model.page.logs
            }
        }
    }
</script>