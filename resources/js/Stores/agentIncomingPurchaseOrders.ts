/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

import { defineStore } from "pinia"
import { router } from "@inertiajs/vue3"
import { alertNewAgentPurchaseOrder, type NewAgentPurchaseOrder } from "@/Composables/useNotificationSound"

let stopRefreshingOnNavigation: (() => void) | null = null

export const useAgentIncomingPurchaseOrders = defineStore("agent-incoming-purchase-orders", {
	state: () => ({
		count: null as number | null,
		organisation: null as string | null,
		channel: null as string | null,
	}),
	actions: {
		async fetch() {
			if (!this.organisation) return

			try {
				const response = await window.fetch(route("grp.org.agent.incoming_purchase_orders.count", [this.organisation]), {
					headers: { Accept: "application/json" },
				})
				if (!response.ok) return

				const { count, channel } = await response.json()
				this.count = count
				this.subscribe(channel)
			} catch {
				// a missed refresh just leaves the last known number on screen
			}
		},

		subscribe(channel?: string) {
			if (!channel || this.channel === channel || !window.Echo) return

			this.unwatch()
			this.channel = channel
			window.Echo.private(channel).listen(
				".incoming-purchase-orders-changed",
				(event: { new_purchase_order: NewAgentPurchaseOrder | null }) => {
					if (event.new_purchase_order) alertNewAgentPurchaseOrder(event.new_purchase_order)
					this.fetch()
				}
			)
		},

		watch(organisation: string | null) {
			if (this.organisation === organisation) return

			this.unwatch()
			stopRefreshingOnNavigation?.()
			stopRefreshingOnNavigation = null
			this.organisation = organisation
			this.count = null
			if (!organisation) return

			stopRefreshingOnNavigation = router.on("navigate", () => this.fetch())
			this.fetch()
		},

		unwatch() {
			if (this.channel) {
				window.Echo?.leave(this.channel)
				this.channel = null
			}
		},
	},
})
