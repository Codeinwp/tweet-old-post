function logMessage (log, message) {
	return log.concat( message + '\n' )
}

var page = {
	debug: true,
	logs: 'Here starts the log \n\n',
	state: {
		authorizedService: {
			twitter: false,
			facebook: false
		},
		view: 'accounts'
	},
	updateService: function( serviceName, serviceStatus ) {
		if (this.debug) { console.log( 'updateService triggered by', serviceName )
			if (this.debug) { this.logs = logMessage( this.logs, 'updateService triggered by ' + serviceName );
			}
		}
		this.state.authorizedService[serviceName.toLowerCase()] = serviceStatus
	}
}

var tabs = [
	{
		name: 'Accounts',
		slug: 'accounts',
		isActive: true
	},
	{
		name: 'General Settings',
		slug: 'settings',
		isActive: false
	},
	{
		name: 'Post Format',
		slug: 'post',
		isActive: false
	},
	{
		name: 'Custom Schedule',
		slug: 'schedule',
		isActive: false
	},
	{
		name: 'Logs',
		slug: 'logs',
		isActive: false
	}
	]

	module.exports = {
		page: page,
		tabs: tabs
	}
