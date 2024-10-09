export const socketWeblock = (webpage) => {
	let eventData = null // To store event data from the channel

	return {
		eventData,
		actions: {
			unsubscribe() {
				window.Echo.leave(`webpage.${webpage}.preview`)
			},
			subscribe: (callback) => {
				const channel = window.Echo.private(`webpage.${webpage}.preview`).listen(
					".WebpagePreview",
					(event) => {
						if (event && event.webpage) {
							eventData = { ...event.webpage }
							if (callback) {
								callback(eventData)
							}
						}
					}
				)
			},
		},
	}
}

export const SocketHeaderFooter = (website: String) => {
	let eventData = null

	return {
		eventData,
		actions: {
			unsubscribe() {
				window.Echo.leave(`header-footer.${website}.preview`)
			},
			subscribe: (callback: any) => {
				const channel = window.Echo.private(`header-footer.${website}.preview`).listen(
					".WebpagePreview",
					(event) => {
						if (event) {
							eventData = { ...event }
							if (callback) {
								callback(eventData)
							}
						}
					}
				)
			},
			send: (send = "") => {
				const channelName = `header-footer.${website}.preview`
				window.Echo.join(channelName).whisper("otherIsNavigating", { data: send })
			},
		},
	}
}
