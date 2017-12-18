<template>
	<div class="container">
		<h3>Logs</h3>
		<div class="columns">
			<div class="column col-6">
				<pre class="code" data-lang="User Friendly Logs">
					<code>{{ logs }}</code>
				</pre>
			</div>
			<div class="column col-6">
				<pre class="code" data-lang="Verbose Logs">
					<code>{{ logs_verbose }}</code>
				</pre>
			</div>
		</div>
	</div>
</template>

<script>
	module.exports = {
		name: 'logs-view',
		props: [ 'model' ],
		mounted: function () {
			this.$store.dispatch( 'fetchAJAX', { req: 'get_log' } )
		},
		computed: {
			logs: function () {
				return this.$store.state.page.logs
			},
			logs_verbose: function () {
				return this.$store.state.page.logs_verbose
			}
		}
	}
</script>