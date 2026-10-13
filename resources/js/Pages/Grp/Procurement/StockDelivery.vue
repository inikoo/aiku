<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 15 Sept 2022 16:07:20 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import type { Component } from "vue"
import { Head, Link, router } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"

import ConfirmDialog from "primevue/confirmdialog"
import Dialog from "primevue/dialog"
import DatePicker from "primevue/datepicker"
import InputText from "primevue/inputtext"
import { useConfirm } from "primevue/useconfirm"
import { notify } from "@kyvg/vue3-notification"

import PageHeading from "@/Components/Headings/PageHeading.vue"
import Tabs from "@/Components/Navigation/Tabs.vue"
import Timeline from "@/Components/Utils/Timeline.vue"
import StockDeliveryCostingChecklist from "@/Components/Procurement/StockDeliveryCostingChecklist.vue"
import StockDeliveryInvoiceCosting from "@/Components/Procurement/StockDeliveryInvoiceCosting.vue"
import StockDeliveryInvoiceEntry from "@/Components/Procurement/StockDeliveryInvoiceEntry.vue"
import StockDeliveryAgentPayments from "@/Components/Procurement/StockDeliveryAgentPayments.vue"
import StockDeliverySupplierPayments from "@/Components/Procurement/StockDeliverySupplierPayments.vue"
import StockDeliveryServiceInvoices from "@/Components/Procurement/StockDeliveryServiceInvoices.vue"
import AgentContainerInvoicePanel from "@/Components/Procurement/AgentContainerInvoicePanel.vue"
import TableStockDeliveryItems from "@/Components/Tables/Grp/Org/Procurement/TableStockDeliveryItems.vue"
import TableStockDeliveryDiscrepancies from "@/Components/Tables/Grp/Org/Procurement/TableStockDeliveryDiscrepancies.vue"
import StockDeliveryCustoms from "@/Components/Procurement/StockDeliveryCustoms.vue"
import TablePurchaseOrders from "@/Components/Tables/Grp/Org/Procurement/TablePurchaseOrders.vue"
import TableAttachments from "@/Components/Tables/Grp/Helpers/TableAttachments.vue"
import TableProcurementNotes from '@/Components/Tables/Grp/Org/Procurement/TableProcurementNotes.vue'
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import UploadAttachment from "@/Components/Upload/UploadAttachment.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import BoxStatPallet from "@/Components/Pallet/BoxStatPallet.vue"

import { useLocaleStore } from "@/Stores/locale"
import { useTabChange } from "@/Composables/tab-change"
import { capitalize } from "@/Composables/capitalize"

import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"
import { Timeline as TSTimeline } from "@/types/Timeline"

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faSpinnerThird } from "@fad"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faInventory, faWarehouse, faPersonDolly, faBoxUsd, faTruck, faTerminal, faCameraRetro, faPaperclip, faInfoCircle, faHandHoldingBox, faPeopleArrows, faExclamationTriangle, faBoxOpen, faClipboardList, faBalanceScale, faPassport, faCheckCircle, faTimes, faCheck, faSquare, faChevronRight, faChevronDown, faShip, faHashtag, faTrashAlt } from "@fal"
import { faBars, faBoxCheck, faInventory as fasInventory, faShare, faArrowCircleRight, faArrowCircleLeft, faExclamationCircle, faBoxFull, faCheckSquare } from "@fas"

library.add(
	faShip,
	faHashtag,
	faBalanceScale,
	faPassport,
	faInventory,
	faWarehouse,
	faPersonDolly,
	faBoxUsd,
	faCheckCircle,
	faChevronRight,
	faChevronDown,
	faSquare,
	faCheckSquare,
	faTimes,
	faCheck,
	faTruck,
	faTerminal,
	faCameraRetro,
	faPaperclip,
	faInfoCircle,
	faHandHoldingBox,
	faPeopleArrows,
	faExclamationTriangle,
	faBars,
	faBoxCheck,
	fasInventory,
	faShare,
	faArrowCircleRight,
	faArrowCircleLeft,
	faExclamationCircle,
	faBoxOpen,
	faBoxFull,
	faClipboardList
)

const props = defineProps<{
	title: string
	pageHead: PageHeadingTypes
	stock_delivery: {
		state: string
	}
	timelines: {
		[key: string]: TSTimeline
	}
	purchase_orders?: {}
	box_stats: {
		first_block: {
			orderer: {
				id?: number
				slug?: string
				type?: string
				name?: string
			}
			delivery: {
				type: string | null
				incoterm: string | null
				port_of_export: string | null
				port_of_import: string | null
				delivery_address: string | null
				shipper_reference: string | null
				tracking_number: string | null
				shipping_agent: string | null
			}
		}
		second_block: {
			state: string
			total_items: number
			total_received_checked_items: number
			total_placed_items: number
			total_new_org_stocks: number
			show_delivery_discrepancy: boolean
			total_under_delivered_items: number
			total_over_delivered_items: number
			total_possible_unit_mismatch_items: number
			weight: number | null
			volume: number | null
			is_weight_partial: boolean
			is_volume_partial: boolean
			production_time: string | null
			delivery_time: string | null
		}
		third_block: {
			currency: string | null
			org_currency: string | null
			org_exchange: number | string | null
			items: number | string | null
			extra: number | string | null
			shipping: number | string | null
			duties: number | string | null
			tax: number | string | null
			total: number | string
			org_items: number | string
			remove_unbacked_costs_route: routeType | null
		}
		invoice_entry: { route: routeType; is_agent: boolean } | null
		invoice: {
			kind: "agent" | "supplier"
			source: "agent" | "actual" | "estimated"
			reference: string | null
			date: string
			charges_list: { description: string; type?: "freight" | "other"; amount: number }[]
			currency: string
			org_currency: string
			org_exchange: number | null
			goods: number
			charges: number
			total: number
			paid: number | null
			balance_due: number | null
			agent: {
				can_edit: boolean
				charges_approved: boolean
				approve_route: routeType
				deposits: { type: string; reference: string | null; date: string | null; amount: number }[]
				payments: { id: number; date: string; amount: number; reference: string | null; notes: string | null; delete_route: routeType }[]
				payment_store_route: routeType
			} | null
			supplier: InstanceType<typeof StockDeliverySupplierPayments>["$props"]["supplier"] | null
		} | null
	}
	tabs: {
		current: string
		navigation: {}
	}
	attachmentRoutes: {
		attachRoute: routeType
		detachRoute: routeType
	}
	costing: {
		is_costed: boolean
		is_partner: boolean
		viewed_by_agent: boolean
		can_edit: boolean
		can_edit_payments: boolean
		reopened: { at: string, by: string | null, reason: string } | null
		currency: string | null
		checklist: any[]
		org_currency_id: number
		put_away_blocked: string[]
		costs_are_users_job: boolean
		agent_invoice_missing: boolean
		storeCostRoute: routeType
		distributeExtraCostRoute: routeType | null
	}
	items?: {}
	pending_items?: {}
	done_items?: {}
	under_over_delivered?: {}
	customs?: {}
	attachments?: {}
	attachmentScopes: { name: string; code: string }[]
	invoice_costing: InstanceType<typeof StockDeliveryInvoiceCosting>["$props"]["invoices"]
	agentInvoice?: InstanceType<typeof AgentContainerInvoicePanel>["$props"]["data"] | null
	service_invoices: InstanceType<typeof StockDeliveryServiceInvoices>["$props"]["data"] | null
	notes?: {}
	note_store_route?: routeType
	history?: {}
}>()

const locale = useLocaleStore()

const currentTab = ref(props.tabs.current)
const isModalUploadOpen = ref(false)
const costingSection = ref<"invoice" | "landed" | null>(null)
const isCostingVisible = computed(() => !["in_process", "cancelled", "not_received"].includes(props.stock_delivery.state))

const component = computed(() => {
	const components: Component = {
		items: TableStockDeliveryItems,
		pending_items: TableStockDeliveryItems,
		done_items: TableStockDeliveryItems,
		under_over_delivered: TableStockDeliveryDiscrepancies,
		customs: StockDeliveryCustoms,
		purchase_orders: TablePurchaseOrders,
		attachments: TableAttachments,
		notes: TableProcurementNotes,
		history: TableHistories,
	}

	return components[currentTab.value]
})

const metrics = computed(() => {
	const { weight, volume, is_weight_partial, is_volume_partial } = props.box_stats.second_block

	return [
		{
			key: "weight",
			isUnknown: weight === null,
			showMark: weight === null || is_weight_partial,
			text: weight === null ? ctrans("Unknown weight") : `${locale.number(weight)}Kg`,
			tooltip: weight === null ? ctrans("No item has weight data") : ctrans("Some items have unknown weight"),
		},
		{
			key: "volume",
			isUnknown: volume === null,
			showMark: volume === null || is_volume_partial,
			text: volume === null ? ctrans("Unknown CBM") : `${locale.number(volume)} m³`,
			tooltip: volume === null ? ctrans("No item has CBM data") : ctrans("Some items have unknown CBM"),
		},
	]
})

const ordererRoute = computed<string>(() => {
	const orderer = props.box_stats.first_block.orderer
	const routeKey = orderer.slug ?? orderer.id
	const type = orderer.type

	if (!routeKey || !type) return ""

	const organisation = route().params["organisation"]

	switch (type) {
		case "Agent":
			return route("grp.org.procurement.org_agents.show", [organisation, routeKey])
		case "Supplier":
			return route("grp.org.procurement.org_suppliers.show", [organisation, routeKey])
		case "Partner":
			return route("grp.org.procurement.org_partners.show", [organisation, routeKey])
		default:
			return ""
	}
})

const orgPerOrder = computed(() => {
	const { items, org_items, org_exchange } = props.box_stats.third_block
	const deliveryItems = Number(items)
	const orgItems = Number(org_items)

	if (deliveryItems) {
		return orgItems / deliveryItems
	}

	return Number(org_exchange) || null
})

const openCostBlocks = ref<Record<string, boolean>>({})
const costBlocks = computed(() => {
	const { currency, org_currency, items, org_items } = props.box_stats.third_block

	const money = (code: string | null, amount: number) => locale.currencyFormat(code ?? "", amount)

	const sameCurrency = !org_currency || org_currency === currency
	const orgCurrency = org_currency || currency
	const rate = sameCurrency ? 1 : (orgPerOrder.value ?? 1)

	const agentInvoice = props.box_stats.invoice
	const agentInvoiceRate = agentInvoice?.org_exchange ?? null
	const agentInvoiceRow = (label: string, amount: number, isTotal = false) => ({
		label,
		value: agentInvoiceRate === null ? money(agentInvoice!.currency, amount) : money(agentInvoice!.org_currency, amount * agentInvoiceRate),
		sub: agentInvoiceRate === null || agentInvoice!.currency === agentInvoice!.org_currency ? null : money(agentInvoice!.currency, amount),
		isTotal,
	})

	const goods = Number(items) || 0
	const orgGoods = sameCurrency ? goods : Number(org_items)
	const goodsRow = (label: string, isTotal = false) => ({ label, value: money(orgCurrency, orgGoods), sub: sameCurrency ? null : money(currency, goods), isTotal })

	const firstBlock = agentInvoice
		? {
			key: "invoice",
			columns: agentInvoiceRate === null || agentInvoice.currency === agentInvoice.org_currency ? null : [agentInvoice.org_currency, agentInvoice.currency],
			title: agentInvoice.kind === "agent" ? ctrans("Agent invoice") : ctrans("Supplier invoice"),
			reference: agentInvoice.reference ?? "",
			badge: agentInvoice.source === "estimated" ? ctrans("Estimated") : null,
			badgeTooltip: ctrans("No invoice was entered for this delivery; these figures are estimated from what the delivery recorded"),
			rows: [
				agentInvoiceRow(ctrans("Goods"), agentInvoice.goods),
				{
					...agentInvoiceRow(agentInvoice.kind === "agent" ? ctrans("Agent charges") : ctrans("Supplier charges"), agentInvoice.charges),
					note: agentInvoice.goods > 0 ? `${(agentInvoice.charges / agentInvoice.goods * 100).toFixed(1)}%` : "",
				},
				...(agentInvoice.paid === null || agentInvoice.balance_due === null ? [] : [
					agentInvoiceRow(ctrans("Deposits and payments"), -agentInvoice.paid),
				]),
			],
		}
		: {
			key: "invoice",
			columns: sameCurrency ? null : [orgCurrency ?? "", currency ?? ""],
			title: ctrans("Agent or supplier invoice"),
			reference: "",
			badge: ctrans("Not entered"),
			badgeTooltip: ctrans("The invoice of this delivery has not been entered yet; the goods are valued at what the delivery recorded"),
			rows: [goodsRow(ctrans("Goods"))],
		}

	const deliveryAmount = (key: string) => Number(props.box_stats.third_block[key as "extra"]) || 0
	const agentInvoiceInDelivery = agentInvoice?.currency === currency && agentInvoice.source !== "estimated"
		? Math.min(agentInvoice.charges, deliveryAmount("shipping") + deliveryAmount("extra"))
		: 0
	const localCosts = [
		{ label: ctrans("Customs duties"), amount: deliveryAmount("duties") },
		{ label: ctrans("Import tax"), amount: deliveryAmount("tax") },
		{ label: ctrans("Local shipping and other"), amount: deliveryAmount("shipping") + deliveryAmount("extra") - agentInvoiceInDelivery },
	].filter(row => Math.abs(row.amount) > 0.005)
	const localTotal = localCosts.reduce((sum, row) => sum + row.amount, 0) * rate
	const invoiceGoods = agentInvoice ? (agentInvoiceRate === null ? 0 : agentInvoice.goods * agentInvoiceRate) : orgGoods
	const invoiceTotal = agentInvoice ? (agentInvoiceRate === null ? 0 : agentInvoice.total * agentInvoiceRate) : orgGoods

	const percentOfGoods = (amount: number) => (amount / invoiceGoods * 100).toFixed(1)
	const costLine = (label: string, amount: number) => ({
		label,
		value: money(orgCurrency, amount),
		sub: null as string | null,
		isTotal: false,
		note: invoiceGoods > 0 ? `${percentOfGoods(amount)}%` : "",
	})
	const serviceInvoiceRows = props.service_invoices?.list ?? []
	const unpaidLocal = serviceInvoiceRows
		.filter(row => !row.paid_at)
		.reduce((sum, row) => sum + (row.allocations.find(allocation => allocation.stock_delivery_id === props.service_invoices?.stock_delivery_id)?.amount ?? 0) * Number(row.exchange), 0)
	const invoiceBalance = agentInvoice && agentInvoice.balance_due !== null && agentInvoiceRate !== null ? agentInvoice.balance_due * agentInvoiceRate : null
	const pendingLocalCosts = props.costing.checklist.filter(row => row.type !== "agent_invoice" && !row.received_at && !row.is_na)
	const waiting = (rows: typeof pendingLocalCosts) => rows.filter(row => row.is_waiting_for_invoice).map(row => `${row.label}: ${row.waiting_label}`)
	const missingLocalCosts = pendingLocalCosts.filter(row => !row.is_waiting_for_invoice).map(row => row.label)
	const localCostsStatus = [
		...waiting(pendingLocalCosts),
		...(missingLocalCosts.length ? [ctrans("Missing: :costs", { costs: missingLocalCosts.join(", ") })] : []),
	].join(" · ")
	const waitingInvoice = waiting(props.costing.checklist.filter(row => row.type === "agent_invoice" && !row.received_at))[0]
	const estimates = props.costing.checklist.filter(row => row.is_estimate && !row.is_na).map(row => `${ctrans("Estimated")} ${row.label}: ${row.waiting_label}`)
	const isRising = pendingLocalCosts.some(row => row.is_waiting_for_invoice)
	const status = (missing: string | null, done: string) =>
		!isCostingVisible.value || props.costing.is_partner ? null : { isMissing: !!missing, text: missing ?? done }

	const invoiceBlock = {
		...firstBlock,
		summary: money(orgCurrency, invoiceTotal),
		summarySub: agentInvoice
			? (agentInvoiceRate === null || agentInvoice.currency === agentInvoice.org_currency ? "" : money(agentInvoice.currency, agentInvoice.total))
			: (sameCurrency ? "" : money(currency, goods)),
		detail: "",
		balance: invoiceBalance === null ? "" : money(orgCurrency, invoiceBalance),
		balanceSub: agentInvoice && agentInvoice.balance_due !== null && agentInvoice.currency !== agentInvoice.org_currency ? money(agentInvoice.currency, agentInvoice.balance_due) : "",
		status: status(props.costing.agent_invoice_missing ? (waitingInvoice ?? ctrans("Invoice not received yet")) : null, ctrans("Invoice received")),
	}

	if (props.costing.viewed_by_agent) {
		return [invoiceBlock]
	}

	return [
		invoiceBlock,
		{
			key: "landed",
			summary: money(orgCurrency, localTotal),
			summarySub: "",
			balance: serviceInvoiceRows.length ? money(orgCurrency, unpaidLocal) : "",
			detail: [
				...(serviceInvoiceRows.length ? [ctrans(":count service invoices, :unpaid unpaid", { count: String(serviceInvoiceRows.length), unpaid: String(serviceInvoiceRows.filter(row => !row.paid_at).length) })] : []),
				...(estimates.length ? [ctrans(":count estimated", { count: String(estimates.length) })] : []),
			].join(" · "),
			status: status([localCostsStatus, ...estimates.filter((text, index) => props.costing.checklist.filter(row => row.is_estimate && !row.is_na)[index].type !== "agent_invoice")].filter(Boolean).join(" · ") || null, ctrans("Local costs complete")),
			title: ctrans("Landed costs"),
			rows: localCosts.map(row => ({
				label: row.label,
				value: money(orgCurrency, row.amount * rate),
				sub: null as string | null,
				isTotal: false,
				note: invoiceGoods > 0 ? `${(row.amount * rate / invoiceGoods * 100).toFixed(1)}%` : "",
				removable: Boolean(props.box_stats.third_block.remove_unbacked_costs_route),
			})),
		},
		{
			key: "total",
			summary: money(orgCurrency, invoiceTotal + localTotal),
			summarySub: "",
			balance: invoiceBalance === null && !serviceInvoiceRows.length ? "" : money(orgCurrency, (invoiceBalance ?? 0) + unpaidLocal),
			detail: invoiceGoods > 0
				? ctrans("for every :one of goods, :extra of extra costs", { one: String(money(orgCurrency, 1)), extra: String(money(orgCurrency, (invoiceTotal + localTotal - invoiceGoods) / invoiceGoods)) })
				: "",
			percent: invoiceGoods > 0 ? `+${percentOfGoods(invoiceTotal + localTotal - invoiceGoods)}%` : "",
			status: props.costing.put_away_blocked.length
				? status(ctrans("Put-away blocked until these costs are in: :costs", { costs: props.costing.put_away_blocked.join(", ") }), "")
				: props.costing.is_costed
				? status(estimates.length ? ctrans("Costed with an estimate: the real invoice is still to come") : null, ctrans("Costed"))
				: (isRising ? status(ctrans("This total will increase: an invoice is still to come"), "") : null),
			title: ctrans("Landed total"),
			rows: [
				{ label: ctrans("Goods"), value: money(orgCurrency, invoiceGoods), sub: null as string | null, isTotal: false, note: "" },
				...(agentInvoiceInDelivery > 0 || !agentInvoice || agentInvoiceRate === null ? [] : agentInvoice.charges_list).map(charge => ({ ...costLine(charge.description, charge.amount * (agentInvoiceRate ?? 1)), tag: agentInvoice?.kind === "supplier" ? ctrans("Supplier") : ctrans("Agent") })),
				...props.costing.checklist
					.filter(row => row.type !== "agent_invoice" && !row.is_na)
					.map(row => {
						const orgAmount = row.org_amount ?? (row.amount != null && row.currency_id === props.costing.org_currency_id ? Number(row.amount) : null)

						return orgAmount != null
							? costLine(row.is_estimate ? `${row.label} (${ctrans("estimate")})` : row.label, Number(orgAmount))
							: { label: row.label, value: "—", sub: null as string | null, isTotal: false, note: row.waiting_label ?? ctrans("missing") }
					}),
			],
		},
	]
})

const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const confirm = useConfirm()

const removingUnbackedCosts = ref(false)

const removeUnbackedCosts = () => {
	const removeRoute = props.box_stats.third_block.remove_unbacked_costs_route
	if (!removeRoute) {
		return
	}

	confirm.require({
		group: "stock-delivery",
		message: ctrans("These costs are not from any invoice of this delivery: they were estimated before it had a costing checklist. Removing them leaves the landed costs to the invoices you add."),
		header: ctrans("Remove the estimated costs"),
		rejectProps: { label: ctrans("Cancel") },
		acceptProps: { label: ctrans("Remove"), severity: "danger" },
		accept: () => router.delete(route(removeRoute.name, removeRoute.parameters), {
			preserveScroll: true,
			onStart: () => (removingUnbackedCosts.value = true),
			onFinish: () => (removingUnbackedCosts.value = false),
			onError: (errors) => notify({ title: ctrans("Something went wrong"), text: Object.values(errors).join(" "), type: "error" }),
		}),
	})
}
const deleteLoading = ref(false)
const dispatchLoading = ref(false)
const undispatchLoading = ref(false)
const receiveLoading = ref(false)
const unreceiveLoading = ref(false)
const cancelLoading = ref(false)

const confirmDispatchStockDelivery = (action: any) => {
	confirm.require({
		group: "stock-delivery",
		message: props.costing.agent_invoice_missing
			? ctrans("The agent invoice has not been received yet. Are you sure you want to mark this stock delivery as dispatched?")
			: ctrans("Are you sure you want to mark this stock delivery as dispatched?"),
		header: ctrans("Dispatch Stock Delivery"),
		rejectProps: { label: ctrans("Cancel"), severity: "secondary", outlined: true },
		acceptProps: { label: ctrans("Mark as Dispatched"), severity: "primary" },
		accept: () => {
			router.patch(route(action.route.name, action.route.parameters), {}, {
				onStart: () => { dispatchLoading.value = true },
				onFinish: () => { dispatchLoading.value = false },
				onError: (errors) => {
					notify({
						title: ctrans("Something went wrong"),
						text: errors?.invoice ?? ctrans("Failed to dispatch stock delivery"),
						type: "error",
					})
				},
			})
		},
	})
}

const confirmUndispatchStockDelivery = (action: any) => {
	confirm.require({
		group: "stock-delivery",
		message: ctrans("Are you sure you want to unmark this stock delivery as dispatched? It will be reverted to its previous state."),
		header: ctrans("Unmark as Dispatched"),
		rejectProps: { label: ctrans("Cancel"), severity: "secondary", outlined: true },
		acceptProps: { label: ctrans("Unmark as Dispatched"), severity: "primary" },
		accept: () => {
			router.patch(route(action.route.name, action.route.parameters), {}, {
				onStart: () => { undispatchLoading.value = true },
				onFinish: () => { undispatchLoading.value = false },
				onError: () => {
					notify({
						title: ctrans("Something went wrong"),
						text: ctrans("Failed to unmark stock delivery as dispatched"),
						type: "error",
					})
				},
			})
		},
	})
}

const receiveWithoutCostsAction = ref<any>(null)
const receiveReason = ref("")
const receiveAcknowledged = ref(false)
const canReceiveWithoutCosts = computed(() => receiveReason.value.trim().length >= 5 && (!props.costing.costs_are_users_job || receiveAcknowledged.value))

const receiveStockDelivery = (action: any, data: { reason?: string } = {}) =>
	router.patch(route(action.route.name, action.route.parameters), data, {
		onStart: () => { receiveLoading.value = true },
		onFinish: () => { receiveLoading.value = false },
		onSuccess: () => { receiveWithoutCostsAction.value = null; receiveReason.value = "" },
		onError: (errors: Record<string, string>) => {
			notify({
				title: ctrans("Something went wrong"),
				text: Object.values(errors)[0] ?? ctrans("Failed to receive stock delivery"),
				type: "error",
			})
		},
	})

const confirmReceiveStockDelivery = (action: any) => {
	if (props.costing.put_away_blocked.length) {
		receiveReason.value = ""
		receiveAcknowledged.value = false
		receiveWithoutCostsAction.value = action
		return
	}

	confirm.require({
		group: "stock-delivery",
		message: ctrans("Are you sure you want to mark this stock delivery as received? This can not be reverted."),
		header: ctrans("Receive Stock Delivery"),
		rejectProps: { label: ctrans("Cancel"), severity: "secondary", outlined: true },
		acceptProps: { label: ctrans("Mark as Received"), severity: "primary" },
		accept: () => receiveStockDelivery(action),
	})
}

const confirmUnreceiveStockDelivery = (action: any) => {
	confirm.require({
		group: "stock-delivery",
		message: ctrans("Are you sure you want to unmark this stock delivery as received? It will be reverted to its previous state."),
		header: ctrans("Unmark as Received"),
		rejectProps: { label: ctrans("Cancel"), severity: "secondary", outlined: true },
		acceptProps: { label: ctrans("Unmark as Received"), severity: "primary" },
		accept: () => {
			router.patch(route(action.route.name, action.route.parameters), {}, {
				onStart: () => { unreceiveLoading.value = true },
				onFinish: () => { unreceiveLoading.value = false },
				onError: () => {
					notify({
						title: ctrans("Something went wrong"),
						text: ctrans("Failed to unmark stock delivery as received"),
						type: "error",
					})
				},
			})
		},
	})
}

const confirmCancelStockDelivery = (action: any) => {
	confirm.require({
		group: "stock-delivery",
		message: ctrans("Are you sure you want to cancel this stock delivery? Its items will be emptied and the purchase order will allow a new delivery."),
		header: ctrans("Cancel Stock Delivery"),
		rejectProps: { label: ctrans("Cancel"), severity: "secondary", outlined: true },
		acceptProps: { label: ctrans("Cancel Stock Delivery"), severity: "danger" },
		accept: () => {
			router.patch(route(action.route.name, action.route.parameters), {}, {
				onStart: () => { cancelLoading.value = true },
				onFinish: () => { cancelLoading.value = false },
				onError: () => {
					notify({
						title: ctrans("Something went wrong"),
						text: ctrans("Failed to cancel stock delivery"),
						type: "error",
					})
				},
			})
		},
	})
}

const estimatedDeliveryDateAction = ref<any>(null)
const estimatedReceivingDate = ref<Date | null>(null)
const estimatedDeliveryDateLoading = ref(false)

const openEstimatedDeliveryDateModal = (action: any) => {
	estimatedReceivingDate.value = action.estimated_receiving_date
		? new Date(`${String(action.estimated_receiving_date).slice(0, 10)}T00:00:00`)
		: null
	estimatedDeliveryDateAction.value = action
}

const saveEstimatedDeliveryDate = () => {
	const action = estimatedDeliveryDateAction.value
	const date = estimatedReceivingDate.value

	router.patch(route(action.route.name, action.route.parameters), {
		estimated_receiving_date: date
			? `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`
			: null,
	}, {
		preserveScroll: true,
		onStart: () => { estimatedDeliveryDateLoading.value = true },
		onSuccess: () => { estimatedDeliveryDateAction.value = null },
		onFinish: () => { estimatedDeliveryDateLoading.value = false },
		onError: () => {
			notify({
				title: ctrans("Something went wrong"),
				text: ctrans("Failed to update estimated delivery date"),
				type: "error",
			})
		},
	})
}

const startCostingLoading = ref(false)

const confirmStartStockDeliveryCosting = (action: any) => {
	confirm.require({
		group: "stock-delivery",
		message: ctrans("Are you sure you want to place this stock delivery? This is its final state."),
		header: ctrans("Place stock delivery"),
		rejectProps: { label: ctrans("Cancel"), severity: "secondary", outlined: true },
		acceptProps: { label: ctrans("Place"), severity: "primary" },
		accept: () => {
			router.patch(route(action.route.name, action.route.parameters), {}, {
				onStart: () => { startCostingLoading.value = true },
				onFinish: () => { startCostingLoading.value = false },
				onError: () => {
					notify({
						title: ctrans("Something went wrong"),
						text: ctrans("Failed to start checking the costs"),
						type: "error",
					})
				},
			})
		},
	})
}

const updateCostingLoading = ref(false)

const patchCosting = (action: any, data: Record<string, string>, failure: string) => {
	router.patch(route(action.route.name, action.route.parameters), data, {
		preserveScroll: true,
		onStart: () => { updateCostingLoading.value = true },
		onFinish: () => { updateCostingLoading.value = false },
		onError: (errors: Record<string, string>) => {
			notify({
				title: ctrans("Something went wrong"),
				text: Object.values(errors)[0] || failure,
				type: "error",
			})
		},
	})
}

const confirmReopenStockDeliveryCosting = (action: any) => {
	confirm.require({
		group: "stock-delivery",
		message: ctrans("This delivery is costed and its stock is already in the warehouse. Changing its costs changes the value of that stock: when you finish, the stock is revalued and its stock history is rebuilt from the day it was put away, so past stock values and reports will change. Every change is recorded with your name."),
		header: ctrans("Update costing"),
		rejectProps: { label: ctrans("Cancel"), severity: "secondary", outlined: true },
		acceptProps: { label: ctrans("Update costing"), severity: "danger" },
		accept: () => {
			const reason = window.prompt(ctrans("Why does the costing need to change?"))
			if (!reason?.trim()) {
				return
			}
			patchCosting(action, { reason: reason.trim() }, ctrans("Failed to update the costing"))
		},
	})
}

const confirmFinishStockDeliveryCosting = (action: any) => {
	confirm.require({
		group: "stock-delivery",
		message: ctrans("The stock put away from this delivery will be revalued with the new costs and its stock history rebuilt from the day it was put away."),
		header: ctrans("Finish costing"),
		rejectProps: { label: ctrans("Cancel"), severity: "secondary", outlined: true },
		acceptProps: { label: ctrans("Finish costing"), severity: "primary" },
		accept: () => patchCosting(action, {}, ctrans("Failed to finish the costing")),
	})
}

const confirmDeleteStockDelivery = (action: any) => {
	confirm.require({
		group: "stock-delivery",
		message: ctrans("Are you sure you want to delete this stock delivery? The purchase order will be reverted to before this delivery."),
		header: ctrans("Delete Stock Delivery"),
		rejectProps: { label: ctrans("Cancel"), severity: "secondary", outlined: true },
		acceptProps: { label: ctrans("Delete"), severity: "danger" },
		accept: () => {
			router.delete(route(action.route.name, action.route.parameters), {
				onStart: () => { deleteLoading.value = true },
				onFinish: () => { deleteLoading.value = false },
				onError: () => {
					notify({
						title: ctrans("Something went wrong"),
						text: ctrans("Failed to delete stock delivery"),
						type: "error",
					})
				},
			})
		},
	})
}
</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead">
		<template #other>
			<Button
				v-if="currentTab === 'attachments'"
				label="Attach"
				icon="upload"
				@click="() => (isModalUploadOpen = true)"
			/>
		</template>

		<template #button-dispatch-stock-delivery="{ action }">
			<Button
				:style="action.style"
				:label="action.label"
				:icon="action.icon"
				:tooltip="action.tooltip"
				:loading="dispatchLoading"
				@click="() => confirmDispatchStockDelivery(action)"
			/>
		</template>

		<template #button-undispatch-stock-delivery="{ action }">
			<Button
				:style="action.style"
				:label="action.label"
				:icon="action.icon"
				:tooltip="action.tooltip"
				:loading="undispatchLoading"
				@click="() => confirmUndispatchStockDelivery(action)"
			/>
		</template>

		<template #button-receive-stock-delivery="{ action }">
			<Button
				:style="action.style"
				:label="action.label"
				:icon="action.icon"
				:tooltip="action.tooltip"
				:loading="receiveLoading"
				@click="() => confirmReceiveStockDelivery(action)"
			/>
		</template>

		<template #button-unreceive-stock-delivery="{ action }">
			<Button
				:style="action.style"
				:label="action.label"
				:icon="action.icon"
				:tooltip="action.tooltip"
				:loading="unreceiveLoading"
				@click="() => confirmUnreceiveStockDelivery(action)"
			/>
		</template>

		<template #button-cancel-stock-delivery="{ action }">
			<Button
				:style="action.style"
				:label="action.label"
				:icon="action.icon"
				:tooltip="action.tooltip"
				:loading="cancelLoading"
				@click="() => confirmCancelStockDelivery(action)"
			/>
		</template>

		<template #button-start-stock-delivery-costing="{ action }">
			<Button
				:style="action.style"
				:label="action.label"
				:icon="action.icon"
				:tooltip="action.tooltip"
				:loading="startCostingLoading"
				@click="() => confirmStartStockDeliveryCosting(action)"
			/>
		</template>

		<template #button-reopen-stock-delivery-costing="{ action }">
			<Button
				:style="action.style"
				:label="action.label"
				:icon="action.icon"
				:tooltip="action.tooltip"
				:loading="updateCostingLoading"
				@click="() => confirmReopenStockDeliveryCosting(action)"
			/>
		</template>

		<template #button-finish-stock-delivery-costing="{ action }">
			<Button
				:style="action.style"
				:label="action.label"
				:icon="action.icon"
				:tooltip="action.tooltip"
				:loading="updateCostingLoading"
				@click="() => confirmFinishStockDeliveryCosting(action)"
			/>
		</template>

		<template #button-edit-estimated-delivery-date="{ action }">
			<Button
				:style="action.style"
				:label="action.label"
				:icon="action.icon"
				:tooltip="action.tooltip"
				:loading="estimatedDeliveryDateLoading"
				@click="() => openEstimatedDeliveryDateModal(action)"
			/>
		</template>

		<template #button-delete-stock-delivery="{ action }">
			<Button
				:style="action.style"
				:label="action.label"
				:icon="action.icon"
				:tooltip="action.tooltip"
				:loading="deleteLoading"
				@click="() => confirmDeleteStockDelivery(action)"
			/>
		</template>
	</PageHeading>

	<!-- Stock Delivery Timeline -->
	<div v-if="timelines" class="py-2 border-b border-gray-300">
		<Timeline
			:options="timelines"
			:state="stock_delivery.state"
			:slidesPerView="6"
			:format-time="'MMMM d yyyy, HH:mm'"
		/>
	</div>

	<AgentContainerInvoicePanel v-if="agentInvoice" :data="agentInvoice" :currencyCode="box_stats.third_block.currency ?? ''" class="mb-4" />

	<div class="grid grid-cols-2 xl:grid-cols-4 text-gray-500 divide-x divide-gray-300 border-b border-gray-300">
		<!-- First Block -->
		<BoxStatPallet class="p-4">
			<div class="flex flex-col gap-4">
				<!-- Orderer -->
				<div v-if="box_stats.first_block.orderer.name" class="flex items-center gap-2">
					<dt>
						<FontAwesomeIcon
							v-tooltip="ctrans(box_stats.first_block.orderer.type ?? '')"
							icon="fal fa-hand-holding-box"
							aria-hidden="true"
							fixed-width
						/>
					</dt>
					<dd>
						<Link v-if="ordererRoute" :href="ordererRoute" class="primaryLink">
							{{ box_stats.first_block.orderer.name }}
						</Link>
						<span v-else>{{ box_stats.first_block.orderer.name }}</span>
					</dd>
				</div>

				<!-- Delivery terms -->
				<div v-if="box_stats.first_block.delivery.type === 'container'">
					<div class="flex items-center gap-2">
						<dt>
							<FontAwesomeIcon
								v-tooltip="ctrans('Incoterm')"
								icon="fas fa-share"
								aria-hidden="true"
								fixed-width
							/>
						</dt>
						<dd v-if="box_stats.first_block.delivery.incoterm">{{ box_stats.first_block.delivery.incoterm }}</dd>
						<dd v-else class="flex items-center gap-1 text-red-500 text-sm italic">
							<FontAwesomeIcon icon="fas fa-exclamation-circle" aria-hidden="true" fixed-width />
							<span>{{ ctrans("Incoterm not set") }}</span>
						</dd>
					</div>

					<div class="flex items-center gap-2">
						<dt>
							<FontAwesomeIcon
								v-tooltip="ctrans('Port of export')"
								icon="fas fa-arrow-circle-right"
								aria-hidden="true"
								fixed-width
							/>
						</dt>
						<dd v-if="box_stats.first_block.delivery.port_of_export">{{ box_stats.first_block.delivery.port_of_export }}</dd>
						<dd v-else class="flex items-center gap-1 text-red-500 text-sm italic">
							<FontAwesomeIcon icon="fas fa-exclamation-circle" aria-hidden="true" fixed-width />
							<span>{{ ctrans("Port of export not set") }}</span>
						</dd>
					</div>

					<div class="flex items-center gap-2">
						<dt>
							<FontAwesomeIcon
								v-tooltip="ctrans('Port of import')"
								icon="fas fa-arrow-circle-left"
								aria-hidden="true"
								fixed-width
							/>
						</dt>
						<dd v-if="box_stats.first_block.delivery.port_of_import">{{ box_stats.first_block.delivery.port_of_import }}</dd>
						<dd v-else class="flex items-center gap-1 text-red-500 text-sm italic">
							<FontAwesomeIcon icon="fas fa-exclamation-circle" aria-hidden="true" fixed-width />
							<span>{{ ctrans("Port of import not set") }}</span>
						</dd>
					</div>
				</div>

				<div v-if="box_stats.first_block.delivery.tracking_number" class="flex items-center gap-2">
					<dt>
						<FontAwesomeIcon v-tooltip="ctrans('Tracking number')" icon="fal fa-ship" aria-hidden="true" fixed-width />
					</dt>
					<dd>
						<a :href="`https://www.track-trace.com/container?number=${encodeURIComponent(box_stats.first_block.delivery.tracking_number)}`" target="_blank" rel="noopener" class="underline decoration-dotted underline-offset-2">{{ box_stats.first_block.delivery.tracking_number }}</a>
					</dd>
				</div>
				<div v-if="box_stats.first_block.delivery.shipper_reference" class="flex items-center gap-2">
					<dt>
						<FontAwesomeIcon v-tooltip="ctrans('Shipper reference')" icon="fal fa-hashtag" aria-hidden="true" fixed-width />
					</dt>
					<dd>{{ box_stats.first_block.delivery.shipper_reference }}</dd>
				</div>
				<div v-if="box_stats.first_block.delivery.shipping_agent" class="flex items-center gap-2">
					<dt>
						<FontAwesomeIcon v-tooltip="ctrans('Shipping agent')" icon="fal fa-people-arrows" aria-hidden="true" fixed-width />
					</dt>
					<dd>{{ box_stats.first_block.delivery.shipping_agent }}</dd>
				</div>

				<!-- Deliver to -->
				<div class="pt-2 text-sm">
					<div class="text-gray-400">{{ ctrans("Deliver to") }}:</div>
					<div v-if="box_stats.first_block.delivery.delivery_address" class="text-xs whitespace-pre-line">{{ box_stats.first_block.delivery.delivery_address }}</div>
					<div v-else class="flex items-center gap-1 text-red-500 text-xs italic">
						<FontAwesomeIcon icon="fas fa-exclamation-circle" aria-hidden="true" fixed-width />
						<span>{{ ctrans("Delivery address not set") }}</span>
					</div>
				</div>
			</div>
		</BoxStatPallet>

		<!-- Second Block -->
		<BoxStatPallet class="p-4">
			<div class="flex justify-center items-center gap-2">
				<FontAwesomeIcon
					v-tooltip="ctrans('Stock Delivery')"
					icon="fal fa-people-arrows"
					class="text-gray-400"
					fixed-width
					aria-hidden="true"
				/>
				<span>{{ box_stats.second_block.state }}</span>
			</div>

			<hr class="-mx-4 my-1 border-t border-gray-200" />

			<!-- Todo: not sure in which states production/delivery time should appear, so far only known when the purchase order is cancelled, hidden for now -->
			<template v-if="false">
				<div class="space-y-1 text-sm">
					<div class="flex items-center justify-between gap-4">
						<span>{{ ctrans("Production time") }}</span>
						<span :class="box_stats.second_block.production_time ? '' : 'italic text-gray-400'">
							{{ box_stats.second_block.production_time ?? ctrans("Unknown") }}
						</span>
					</div>
					<div class="flex items-center justify-between gap-4">
						<span>{{ ctrans("Delivery time") }}</span>
						<span :class="box_stats.second_block.delivery_time ? '' : 'italic text-gray-400'">
							{{ box_stats.second_block.delivery_time ?? ctrans("Unknown") }}
						</span>
					</div>
				</div>

				<hr class="-mx-4 my-1 border-t border-gray-200" />
			</template>

			<div class="flex justify-center gap-4">
				<div class="flex items-center gap-1">
					<FontAwesomeIcon v-tooltip="ctrans('Items')" icon="fas fa-bars" aria-hidden="true" fixed-width />
					<span>{{ box_stats.second_block.total_items }}</span>
				</div>

				<div class="flex items-center gap-1">
					<FontAwesomeIcon v-tooltip="ctrans('Received & checked items')" icon="fas fa-box-check" aria-hidden="true" fixed-width />
					<span>{{ box_stats.second_block.total_received_checked_items }}</span>
				</div>

				<div class="flex items-center gap-1">
					<FontAwesomeIcon v-tooltip="ctrans('Placed items')" icon="fas fa-inventory" aria-hidden="true" fixed-width />
					<span>{{ box_stats.second_block.total_placed_items }}</span>
				</div>

				<span v-if="box_stats.second_block.total_new_org_stocks > 0"
					v-tooltip="ctrans('Items that have never been in stock')"
					class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
					{{ ctrans(':count new', { count: box_stats.second_block.total_new_org_stocks }) }}
				</span>
			</div>

			<div class="mt-2 grid grid-cols-2 gap-2 text-sm">
				<div
					v-for="metric in metrics"
					:key="metric.key"
					class="flex items-center justify-center gap-1"
					:class="metric.isUnknown ? 'italic text-red-500' : ''"
				>
					<FontAwesomeIcon
						v-if="metric.showMark"
						v-tooltip="metric.tooltip"
						icon="fas fa-exclamation-circle"
						:class="metric.isUnknown ? 'text-red-500' : 'text-orange-500'"
						aria-hidden="true"
						fixed-width
					/>
					<span>{{ metric.text }}</span>
				</div>
			</div>

			<div v-if="box_stats.second_block.show_delivery_discrepancy" class="mt-2 flex justify-center gap-4">
				<div
					class="flex items-center gap-1"
					:class="box_stats.second_block.total_under_delivered_items ? 'text-orange-500' : ''"
				>
					<FontAwesomeIcon
						v-tooltip="ctrans('Items under delivered')"
						icon="fal fa-box-open"
						aria-hidden="true"
						fixed-width
					/>
					<span>{{ box_stats.second_block.total_under_delivered_items }}</span>
				</div>

				<div
					class="flex items-center gap-1"
					:class="box_stats.second_block.total_over_delivered_items ? 'text-orange-500' : ''"
				>
					<FontAwesomeIcon
						v-tooltip="ctrans('Items over delivered')"
						icon="fas fa-box-full"
						aria-hidden="true"
						fixed-width
					/>
					<span>{{ box_stats.second_block.total_over_delivered_items }}</span>
				</div>

				<div
					v-if="box_stats.second_block.total_possible_unit_mismatch_items"
					class="flex items-center gap-1 text-orange-500"
				>
					<FontAwesomeIcon
						v-tooltip="ctrans('Items whose count is a clean multiple of what was expected: probably invoiced in another unit')"
						icon="fal fa-balance-scale"
						aria-hidden="true"
						fixed-width
					/>
					<span>{{ box_stats.second_block.total_possible_unit_mismatch_items }}</span>
				</div>
			</div>
		</BoxStatPallet>

		<!-- Third Block: costs -->
		<BoxStatPallet class="col-span-2">
			<div>
				<table class="w-full text-sm">
					<thead>
						<tr class="bg-gray-50 text-xs text-gray-600">
							<th class="px-3 py-2 text-left font-medium">{{ ctrans("Costing") }}</th>
							<th class="px-3 py-2 text-left font-medium">{{ ctrans("Status") }}</th>
							<th class="px-3 py-2 text-right font-medium">{{ ctrans("Amount") }}</th>
							<th class="px-3 py-2 text-right font-medium">{{ ctrans("Balance due") }}</th>
						</tr>
					</thead>
					<tbody v-for="(block, blockIndex) in costBlocks" :key="block.key" class="border-t border-gray-200">
						<tr
							class="cursor-pointer hover:bg-gray-50"
							:class="block.key === 'total' ? 'bg-gray-50' : ''"
							:aria-expanded="!!openCostBlocks[block.key]"
							@click="openCostBlocks[block.key] = !openCostBlocks[block.key]"
						>
							<td class="px-3 py-2 align-top">
								<div class="flex items-center gap-2 font-medium text-gray-800">
									<FontAwesomeIcon :icon="openCostBlocks[block.key] ? 'fal fa-chevron-down' : 'fal fa-chevron-right'" class="text-xs text-gray-400" fixed-width aria-hidden="true" />
									{{ block.title }}
									<button
										v-if="block.key === 'landed' && isCostingVisible && !costing.viewed_by_agent"
										type="button"
										class="text-gray-400 hover:text-gray-700"
										:title="ctrans('Edit landed costs')"
										:aria-label="ctrans('Edit landed costs')"
										@click.stop="costingSection = 'landed'"
									>
										<FontAwesomeIcon icon="fal fa-pencil" fixed-width aria-hidden="true" />
									</button>
								</div>
								<div v-if="'reference' in block && block.reference" class="pl-6 text-xs text-gray-600">{{ block.reference }}</div>
								<div v-if="block.detail" class="pl-6 text-xs text-gray-500">{{ block.detail }}</div>
							</td>
							<td class="px-3 py-2 align-top">
								<span v-if="'percent' in block && block.percent" class="float-right text-xs font-medium tabular-nums text-gray-700">{{ block.percent }}</span>
								<span v-if="block.badge && !block.status?.isMissing" class="mr-1.5 inline-flex rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700" v-tooltip="block.badgeTooltip">{{ block.badge }}</span>
								<span
									v-if="block.status"
									class="inline-flex items-start gap-1.5 rounded px-2 py-0.5 text-xs"
									:class="block.status.isMissing ? 'bg-amber-50 text-amber-800' : 'bg-green-50 text-green-800'"
								>
									<FontAwesomeIcon :icon="block.status.isMissing ? 'fal fa-exclamation-triangle' : 'fal fa-check-circle'" class="mt-0.5" fixed-width aria-hidden="true" />
									{{ block.status.text }}
								</span>
							</td>
							<td class="whitespace-nowrap px-3 py-2 text-right align-top tabular-nums">
								<span class="font-semibold text-gray-900">{{ block.summary }}</span>
								<span v-if="block.summarySub" class="block text-xs text-gray-500">{{ block.summarySub }}</span>
							</td>
							<td class="whitespace-nowrap px-3 py-2 text-right align-top tabular-nums text-gray-700">
								{{ block.balance }}
								<span v-if="'balanceSub' in block && block.balanceSub" class="block text-xs text-gray-500">{{ block.balanceSub }}</span>
							</td>
						</tr>
						<template v-if="openCostBlocks[block.key]">
							<tr v-for="(row, rowIndex) in block.rows" :key="rowIndex" class="border-t border-gray-100 text-gray-600">
								<td class="py-1.5 pl-11 pr-3 align-top" :class="row.isTotal ? 'font-medium text-gray-800' : ''">
									<span class="inline-flex items-center gap-1.5 whitespace-nowrap">
										{{ row.label }}
										<span v-if="'tag' in row && row.tag" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">{{ row.tag }}</span>
										<template v-if="'removable' in row && row.removable">
											<span class="rounded bg-amber-100 px-1.5 py-0.5 text-xs text-amber-800">{{ ctrans("Estimated, no invoice") }}</span>
											<button type="button" class="text-red-500 hover:text-red-700" :disabled="removingUnbackedCosts" :title="ctrans('Remove the estimated costs')" :aria-label="ctrans('Remove the estimated costs')" @click="removeUnbackedCosts">
												<FontAwesomeIcon :icon="removingUnbackedCosts ? faSpinnerThird : faTrashAlt" :spin="removingUnbackedCosts" fixed-width aria-hidden="true" />
											</button>
										</template>
									</span>
								</td>
								<td class="px-3 py-1.5 text-right align-top text-xs tabular-nums text-gray-500">{{ 'note' in row ? row.note : '' }}</td>
								<td class="whitespace-nowrap px-3 py-1 text-right tabular-nums" :class="row.isTotal ? 'font-medium text-gray-800' : ''">
									{{ row.value }}
									<span v-if="row.sub" class="block text-xs font-normal text-gray-500">{{ row.sub }}</span>
								</td>
								<td></td>
							</tr>
							<tr v-if="block.key !== 'total'" class="border-t border-gray-100">
								<td colspan="4" class="px-3 pb-3 pl-11">
									<StockDeliveryServiceInvoices v-if="service_invoices && block.key === 'landed'" :data="service_invoices" />
									<div class="flex flex-wrap items-center justify-end gap-x-2">
										<StockDeliveryAgentPayments
											v-if="blockIndex === 0 && box_stats.invoice?.agent"
											:agent="box_stats.invoice.agent"
											:currency="box_stats.invoice.currency"
											:hasCharges="box_stats.invoice.charges_list.length > 0"
											:isEstimate="box_stats.invoice.source === 'estimated'"
										/>
										<StockDeliverySupplierPayments
											v-if="blockIndex === 0 && box_stats.invoice?.supplier && (box_stats.invoice.total > 0 || box_stats.invoice.supplier.payments.length > 0)"
											:supplier="box_stats.invoice.supplier"
											:currency="box_stats.invoice.currency"
											:total="box_stats.invoice.total"
											:orgCurrency="box_stats.invoice.org_currency"
											:orgExchange="box_stats.invoice.org_exchange"
											:isEstimate="box_stats.invoice.source === 'estimated'"
										/>
										<div v-if="isCostingVisible && !costing.viewed_by_agent && blockIndex === 0" class="mt-3">
											<Button
												type="tertiary"
												size="xs"
												:label="ctrans('Invoice')"
												icon="fal fa-box-usd"
												@click="() => (costingSection = 'invoice')"
											/>
										</div>
									</div>
								</td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>
		</BoxStatPallet>
	</div>

	<Dialog
		:visible="!!estimatedDeliveryDateAction"
		modal
		:header="ctrans('Estimated delivery date')"
		:style="{ width: '30rem', maxWidth: 'calc(100vw - 2rem)' }"
		:draggable="false"
		@update:visible="(visible: boolean) => { if (!visible) estimatedDeliveryDateAction = null }"
	>
		<div class="flex flex-col gap-2">
			<label for="stock-delivery-estimated-delivery-date" class="font-medium text-gray-700">
				{{ ctrans("Estimated delivery date") }}
			</label>
			<DatePicker
				v-model="estimatedReceivingDate"
				inputId="stock-delivery-estimated-delivery-date"
				dateFormat="yy-mm-dd"
				showIcon
				showButtonBar
				fluid
			/>
		</div>

		<template #footer>
			<Button :label="ctrans('Cancel')" type="secondary" @click="estimatedDeliveryDateAction = null" />
			<Button :label="ctrans('Save')" type="save" :loading="estimatedDeliveryDateLoading" @click="saveEstimatedDeliveryDate" />
		</template>
	</Dialog>

	<Dialog
		:visible="!!costingSection"
		modal
		dismissableMask
		:closable="false"
		:style="{ width: '64rem' }"
		:breakpoints="{ '1024px': '95vw' }"
		:pt="{ header: { class: '!px-4 !py-2.5 border-b border-gray-200' }, content: { class: '!p-0' } }"
		@update:visible="(visible: boolean) => { if (!visible) costingSection = null }"
	>
		<template #header>
			<div class="flex w-full items-center justify-between gap-3">
				<span class="text-sm font-semibold text-gray-900">
					{{ costingSection === "invoice" ? ctrans("Agent or supplier invoice") : ctrans("Landed costs") }}
				</span>
				<button type="button" class="text-gray-400 hover:text-gray-700" :aria-label="ctrans('Close')" @click="costingSection = null">
					<FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
				</button>
			</div>
		</template>
		<StockDeliveryInvoiceEntry
			v-if="costingSection === 'invoice' && box_stats.invoice_entry"
			:route="box_stats.invoice_entry.route"
			:isAgent="box_stats.invoice_entry.is_agent"
			:invoice="box_stats.invoice"
			:currencyCode="box_stats.invoice?.currency ?? box_stats.third_block.currency ?? ''"
		/>
		<StockDeliveryServiceInvoices v-if="costingSection === 'landed' && service_invoices" :data="service_invoices" uploadable />
		<StockDeliveryCostingChecklist
			v-if="costingSection"
			:section="costingSection"
			:hideRows="costingSection === 'invoice' && !!box_stats.invoice_entry"
			:costing="costing"
			:canEdit="costing.can_edit"
			:canEditPayments="costing.can_edit_payments"
		/>
		<StockDeliveryInvoiceCosting
			v-if="costingSection === 'invoice' && !costing.is_partner"
			:invoices="invoice_costing"
			:canEdit="costing.can_edit"
		/>
	</Dialog>

	<Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
	<component
		:is="component"
		:key="currentTab"
		:data="props[currentTab]"
		:tab="currentTab"
		:costing="currentTab === 'items' ? costing : undefined"
		:detachRoute="attachmentRoutes.detachRoute"
		:storeRoute="currentTab === 'notes' ? note_store_route : undefined"
	/>

	<UploadAttachment
		v-model="isModalUploadOpen"
		scope="attachment"
		:title="{
			label: 'Upload your file',
			information: 'The list of column file: customer_reference, notes, stored_items',
		}"
		progressDescription="Adding Stock Delivery Attachments"
		:attachmentRoutes="attachmentRoutes"
		:options="attachmentScopes"
	/>

	<Dialog
		:visible="!!receiveWithoutCostsAction"
		modal
		:closable="false"
		:style="{ width: '32rem', maxWidth: 'calc(100vw - 2rem)' }"
		:pt="{ header: { class: '!px-4 !py-2.5 border-b border-gray-200' }, content: { class: '!p-4' } }"
		@update:visible="(visible: boolean) => { if (!visible) receiveWithoutCostsAction = null }"
	>
		<template #header>
			<div class="flex items-center gap-2 text-sm font-semibold text-gray-900">
				<FontAwesomeIcon :icon="faExclamationTriangle" :class="costing.costs_are_users_job ? 'text-red-500' : 'text-amber-500'" fixed-width aria-hidden="true" />
				{{ costing.costs_are_users_job ? ctrans("Receiving without its costs") : ctrans("Costs not entered yet") }}
			</div>
		</template>
		<div class="space-y-3 text-sm text-gray-700">
			<p>{{ ctrans("The costs of this delivery have not been entered yet: :costs.", { costs: costing.put_away_blocked.join(", ") }) }}</p>
			<template v-if="costing.costs_are_users_job">
				<p>{{ ctrans("Entering the costs before the goods arrive is part of the procurement job. Without them the goods stay in goods-in, can not be put away or sold, and the stock value and margins are wrong until they are in.") }}</p>
				<p class="font-medium text-red-700">{{ ctrans("Enter the costs, an estimate or not applicable first whenever you can. Receive like this only when it truly can not wait.") }}</p>
			</template>
			<p v-else>{{ ctrans("You can receive and count it, but its goods can not be put away until procurement enters the costs, so nothing is sold at the wrong cost.") }}</p>
			<label class="block">
				<span class="text-gray-600">{{ ctrans("Why is it being received without its costs?") }}</span>
				<InputText v-model="receiveReason" class="mt-1 w-full" autofocus maxlength="255" :placeholder="ctrans('e.g. lorry at the door, invoice not sent yet')" />
			</label>
			<button
				v-if="costing.costs_are_users_job"
				type="button"
				role="checkbox"
				:aria-checked="receiveAcknowledged"
				class="flex items-start gap-2 text-left"
				@click="receiveAcknowledged = !receiveAcknowledged"
			>
				<FontAwesomeIcon :icon="receiveAcknowledged ? 'fas fa-check-square' : 'fal fa-square'" class="mt-0.5 text-lg leading-none" :class="receiveAcknowledged ? 'text-gray-700' : 'text-gray-400'" fixed-width aria-hidden="true" />
				<span>{{ ctrans("I know these costs should have been entered before receiving, and I will get them in.") }}</span>
			</button>
		</div>
		<template #footer>
			<div class="flex w-full flex-wrap items-center justify-between gap-3">
				<p class="max-w-xs text-xs text-gray-500">
					{{ costing.costs_are_users_job
						? ctrans("This is recorded under your name in the history of this delivery and reported to the supply chain managers.")
						: ctrans("Your reason is noted in the history of this delivery and the supply chain team is let know, so they can get the costs in.") }}
				</p>
				<div class="flex gap-2">
					<Button type="tertiary" :label="ctrans('Cancel')" @click="receiveWithoutCostsAction = null">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" :label="costing.costs_are_users_job ? ctrans('Receive anyway') : ctrans('Receive')" :disabled="!canReceiveWithoutCosts" :loading="receiveLoading" @click="receiveStockDelivery(receiveWithoutCostsAction, { reason: receiveReason.trim() })">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>
		</template>
	</Dialog>

	<ConfirmDialog group="stock-delivery" :style="{ width: '28rem', maxWidth: 'calc(100vw - 2rem)' }">
		<template #container="{ message, acceptCallback, rejectCallback }">
			<div class="rounded-lg bg-white p-5">
				<div class="flex items-center gap-2 font-semibold text-gray-900">
					<FontAwesomeIcon :icon="faExclamationTriangle" class="text-orange-500" fixed-width aria-hidden="true" />
					{{ message.header }}
				</div>
				<p class="mt-3 text-sm text-gray-600">{{ message.message }}</p>
				<div class="mt-5 flex justify-end gap-2">
					<Button type="tertiary" :label="message.rejectProps?.label ?? message.rejectLabel ?? ctrans('Cancel')" @click="rejectCallback">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" :label="message.acceptProps?.label ?? message.acceptLabel ?? ctrans('Confirm')" @click="acceptCallback">
						<template #icon>
							<FontAwesomeIcon
								:icon="message.acceptProps?.severity === 'danger' ? 'fal fa-exclamation-triangle' : 'fal fa-check'"
								:class="message.acceptProps?.severity === 'danger' ? 'text-amber-500' : 'text-green-600'"
								fixed-width
								aria-hidden="true"
							/>
						</template>
					</Button>
				</div>
			</div>
		</template>
	</ConfirmDialog>
</template>
