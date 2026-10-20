<script setup lang="ts">
import { computed, inject } from "vue"
import { Head, Link } from "@inertiajs/vue3"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faWallet, faMoneyCheckAlt, faExclamationTriangle, faChevronRight } from "@fal"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import { useFormatTime } from "@/Composables/useFormatTime"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"

library.add(faWallet, faMoneyCheckAlt, faExclamationTriangle, faChevronRight)

interface Money {
	currency_code: string
	amount: number
}

const props = defineProps<{
	title: string
	pageHead: PageHeadingTypes
	routes: { ledger: routeType; deposit_requests: routeType }
	data: {
		summary: {
			balance: Money[]
			clients_owing: number
			open_requests: number
			open_requests_amount: Money[]
			needs_review: number
		}
		wallets: { organisation_id: number; code: string; name: string; currency_code: string; received: number; paid: number; balance: number }[]
		open_requests: { id: number; reference: string | null; client_code: string; currency_code: string; requested_at: string; total: number; outstanding: number }[]
		recently_settled: { id: number; reference: string | null; client_code: string; currency_code: string; settled_at: string; total: number }[]
		recently_paid: { id: number; date: string; amount: number; currency_code: string; client_code: string; purchase_order: string | null; supplier_code: string | null }[]
	}
}>()

const locale = inject("locale", aikuLocaleStructure)

const money = (code: string, amount: number) => locale.currencyFormat(code, amount)
const moneyList = (amounts: Money[]) => (amounts.length ? amounts.map((entry) => money(entry.currency_code, entry.amount)).join(" · ") : null)
const withQuery = (target: routeType, query: Record<string, string>) => ({ ...target, parameters: { ...(target.parameters as object), _query: query } })
const href = (target: routeType) => route(target.name, target.parameters)
const requestName = (request: { reference: string | null; id: number }) => request.reference || ctrans("Request #:id", { id: request.id })

const tiles = computed(() => [
	{
		key: "balance",
		label: ctrans("Clients' balance"),
		icon: "fal fa-wallet",
		value: moneyList(props.data.summary.balance) ?? money("", 0),
		tone: props.data.summary.clients_owing ? "red" : "neutral",
		detail: props.data.summary.clients_owing
			? ctrans(":count clients owe you money", { count: props.data.summary.clients_owing })
			: ctrans("No client owes you money"),
		route: props.routes.ledger,
	},
	{
		key: "requests",
		label: ctrans("Open money requests"),
		icon: "fal fa-money-check-alt",
		value: locale.number(props.data.summary.open_requests),
		tone: props.data.summary.open_requests ? "amber" : "neutral",
		detail: moneyList(props.data.summary.open_requests_amount)
			? ctrans(":value still due", { value: moneyList(props.data.summary.open_requests_amount) })
			: ctrans("Nothing due from clients"),
		route: withQuery(props.routes.deposit_requests, { "elements[state]": "requested" }),
	},
	{
		key: "review",
		label: ctrans("Needs review"),
		icon: "fal fa-exclamation-triangle",
		value: locale.number(props.data.summary.needs_review),
		tone: props.data.summary.needs_review ? "amber" : "neutral",
		detail: props.data.summary.needs_review ? ctrans("Ledger lines to check and correct") : ctrans("Every ledger line is checked"),
		route: withQuery(props.routes.ledger, { "elements[review]": "needs_review" }),
	},
])

const toneClasses: Record<string, string> = {
	red: "bg-red-50 text-red-600",
	amber: "bg-amber-50 text-amber-600",
	neutral: "bg-[--app-accent-soft] text-[--app-accent]",
}
</script>

<template>
	<div>
		<Head :title="capitalize(title)" />
		<PageHeading :data="pageHead" />

		<div class="mx-4 mt-4 space-y-4">
			<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
				<article v-for="tile in tiles" :key="tile.key" class="group rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-gray-300 hover:shadow">
					<Link :href="href(tile.route)" class="block">
						<div class="flex items-center justify-between gap-3">
							<div class="flex min-w-0 items-center gap-2.5">
								<span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md" :class="toneClasses[tile.tone]">
									<FontAwesomeIcon :icon="tile.icon" class="text-sm" fixed-width aria-hidden="true" />
								</span>
								<h2 class="truncate text-sm font-semibold text-gray-600">{{ tile.label }}</h2>
							</div>
							<FontAwesomeIcon icon="fal fa-chevron-right" class="text-xs text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-gray-500" fixed-width aria-hidden="true" />
						</div>
						<div class="mt-3 truncate text-3xl font-semibold tracking-tight tabular-nums" :class="tile.tone === 'red' ? 'text-red-600' : 'text-gray-800'">{{ tile.value }}</div>
					</Link>
					<p class="mt-1 text-xs leading-relaxed text-gray-500">{{ tile.detail }}</p>
				</article>
			</div>

			<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
				<section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
					<header class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
						<h2 class="text-sm font-semibold text-gray-700">{{ ctrans("Balance by client") }}</h2>
						<Link :href="href(routes.ledger)" class="text-xs text-gray-500 hover:text-gray-900 hover:underline">{{ ctrans("Ledger") }}</Link>
					</header>
					<p v-if="!data.wallets.length" class="px-4 py-8 text-center text-sm text-gray-400">{{ ctrans("No clients yet") }}</p>
					<ul v-else class="divide-y divide-gray-100">
						<li v-for="wallet in data.wallets" :key="`${wallet.organisation_id}-${wallet.currency_code}`" class="flex items-center justify-between gap-3 px-4 py-2.5">
							<div class="min-w-0">
								<div class="truncate text-sm font-medium text-gray-900">{{ wallet.name }}</div>
								<div class="text-xs tabular-nums text-gray-500">
									{{ ctrans("Received :received, paid out :paid", { received: money(wallet.currency_code, wallet.received), paid: money(wallet.currency_code, wallet.paid) }) }}
								</div>
							</div>
							<span class="text-sm font-semibold tabular-nums" :class="wallet.balance < 0 ? 'text-red-600' : 'text-gray-800'">{{ money(wallet.currency_code, wallet.balance) }}</span>
						</li>
					</ul>
				</section>

				<section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
					<header class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
						<h2 class="text-sm font-semibold text-gray-700">{{ ctrans("Open money requests") }}</h2>
						<Link :href="href(routes.deposit_requests)" class="text-xs text-gray-500 hover:text-gray-900 hover:underline">{{ ctrans("All requests") }}</Link>
					</header>
					<p v-if="!data.open_requests.length" class="px-4 py-8 text-center text-sm text-gray-400">{{ ctrans("Nothing due from clients") }}</p>
					<ul v-else class="divide-y divide-gray-100">
						<li v-for="request in data.open_requests" :key="request.id" class="flex items-center justify-between gap-3 px-4 py-2.5">
							<div class="min-w-0">
								<div class="truncate whitespace-nowrap text-sm font-medium text-gray-900">{{ requestName(request) }}</div>
								<div class="text-xs text-gray-500">{{ [request.client_code, useFormatTime(request.requested_at)].join(" · ") }}</div>
							</div>
							<div class="text-right">
								<div class="text-sm font-semibold tabular-nums text-amber-600">{{ money(request.currency_code, request.outstanding) }}</div>
								<div v-if="request.outstanding !== request.total" class="text-xs tabular-nums text-gray-400">{{ ctrans("of :total", { total: money(request.currency_code, request.total) }) }}</div>
							</div>
						</li>
					</ul>
				</section>

				<section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
					<header class="border-b border-gray-200 px-4 py-3">
						<h2 class="text-sm font-semibold text-gray-700">{{ ctrans("Recently settled") }}</h2>
					</header>
					<p v-if="!data.recently_settled.length" class="px-4 py-8 text-center text-sm text-gray-400">{{ ctrans("No request has been settled yet") }}</p>
					<ul v-else class="divide-y divide-gray-100">
						<li v-for="request in data.recently_settled" :key="request.id" class="flex items-center justify-between gap-3 px-4 py-2.5">
							<div class="min-w-0">
								<div class="truncate whitespace-nowrap text-sm font-medium text-gray-900">{{ requestName(request) }}</div>
								<div class="text-xs text-gray-500">{{ [request.client_code, useFormatTime(request.settled_at)].join(" · ") }}</div>
							</div>
							<span class="text-sm font-semibold tabular-nums text-green-600">{{ money(request.currency_code, request.total) }}</span>
						</li>
					</ul>
				</section>

				<section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
					<header class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
						<h2 class="text-sm font-semibold text-gray-700">{{ ctrans("Recently paid to suppliers") }}</h2>
						<Link :href="href(withQuery(routes.ledger, { 'elements[direction]': 'out' }))" class="text-xs text-gray-500 hover:text-gray-900 hover:underline">{{ ctrans("Ledger") }}</Link>
					</header>
					<p v-if="!data.recently_paid.length" class="px-4 py-8 text-center text-sm text-gray-400">{{ ctrans("No payment to a supplier recorded yet") }}</p>
					<ul v-else class="divide-y divide-gray-100">
						<li v-for="line in data.recently_paid" :key="line.id" class="flex items-center justify-between gap-3 px-4 py-2.5">
							<div class="min-w-0">
								<div class="truncate whitespace-nowrap text-sm font-medium text-gray-900">{{ line.purchase_order || line.supplier_code || line.client_code }}</div>
								<div class="truncate text-xs text-gray-500">{{ [line.client_code, useFormatTime(line.date, { formatTime: "d MMM yyyy" })].join(" · ") }}</div>
							</div>
							<span class="text-sm font-semibold tabular-nums text-gray-800">{{ money(line.currency_code, line.amount) }}</span>
						</li>
					</ul>
				</section>
			</div>
		</div>
	</div>
</template>
