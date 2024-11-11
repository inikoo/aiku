<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 15 Sept 2022 16:07:20 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, defineAsyncComponent, ref } from "vue"
import type { Component } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import TablePurchaseOrderTransactions from "@/Components/Tables/Grp/Org/Procurement/TablePurchaseOrderTransactions.vue"
import { capitalize } from "@/Composables/capitalize"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import { PageHeading as PageHeadingTS } from "@/types/PageHeading"
import { BoxNote as BoxNoteTS } from "@/types/Components/BoxNotes"
import { routeType } from "@/types/route"
import { trans } from "laravel-vue-i18n"
import { notify } from "@kyvg/vue3-notification"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import PureTextarea from "@/Components/Pure/PureTextarea.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import AlertMessage from "@/Components/Utils/AlertMessage.vue"
import BoxNote from "@/Components/Pallet/BoxNote.vue"
import Popover from "@/Components/Popover.vue"
import TableAttachments from "@/Components/Tables/Grp/Helpers/TableAttachments.vue"
import UploadAttachment from "@/Components/Upload/UploadAttachment.vue"
import { Currency } from "@/types/LayoutRules"
import Modal from '@/Components/Utils/Modal.vue'
import NeedToPay from "@/Components/Utils/NeedToPay.vue"
import OrderSummary from "@/Components/Summary/OrderSummary.vue"
import Timeline from "@/Components/Utils/Timeline.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faExclamationTriangle as fadExclamationTriangle } from "@fad"
import { faExclamationTriangle, faExclamation, faPencil } from "@fas"
import {
	faStickyNote,
	faPaperclip,
	faDollarSign,
	faIdCardAlt,
	faShippingFast,
	faIdCard,
	faEnvelope,
	faPhone,
	faWeight,
	faTruck,
	faFilePdf,
} from "@fal"
library.add(
	faStickyNote,
	faPaperclip,
	fadExclamationTriangle,
	faExclamationTriangle,
	faDollarSign,
	faIdCardAlt,
	faShippingFast,
	faIdCard,
	faEnvelope,
	faPhone,
	faWeight,
	faStickyNote,
	faExclamation,
	faTruck,
	faFilePdf,
	faPaperclip,
	faPencil
)

const props = defineProps<{
	title: string
	pageHead: PageHeadingTS
	tabs: {
		current: string
		navigation: {}
	}
	showcase: {}
	transactions: {}
	history: {}
	alert?: {
		status: string
		title?: string
		description?: string
	}
	notes: {
		note_list: BoxNoteTS[]
	}
	routes: {
		updateOrderRoute: routeType
		products_list: routeType
	}
	attachments?: {}
	attachmentRoutes?: {}
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const component = computed(() => {
	const components: Component = {
		history: TableHistories,
		transactions: TablePurchaseOrderTransactions,
		attachments: TableAttachments,
	}

	return components[currentTab.value]
})

const isLoadingButton = ref<string | boolean>(false)
const isModalUploadOpen = ref(false)
const isSubmitNoteLoading = ref(false)

// Section: add notes (on popup pageheading)
const errorNote = ref("")
const noteToSubmit = ref({
	selectedNote: "",
	value: "",
})
const onSubmitNote2 = async (closePopup: Function) => {
	try {
		router.patch(
			route(props.routes.updateOrderRoute.name, props.routes.updateOrderRoute.parameters),
			{
				[noteToSubmit.value.selectedNote]: noteToSubmit.value.value,
			},
			{
				headers: { "Content-Type": "application/json" },
				onStart: () => (isLoadingButton.value = "submitNote"),
				onError: (error: any) => (errorNote.value = error),
				onFinish: () => (isLoadingButton.value = false),
				onSuccess: () => {
					closePopup(), (noteToSubmit.value.selectedNote = "")
					noteToSubmit.value.value = ""
				},
			}
		)
	} catch (error) {
		notify({
			title: trans("Something went wrong"),
			text: trans("Failed to update the note, try again."),
			type: "error",
		})
	}
}
console.log(props)

const box_stats = ref({
	customer: {
		reference: "#0011",
		contact_name: "andi",
		company_name: "riot",
		email: "andi@gmail.com",
		phone: "087673273723",
		addresses: {
			delivery: "hahah",
			billing: "28382",
		},
	},
	products: {
		payment: {
			routes: {
				fetch_payment_accounts: {
					name: "grp.json.shop.payment-accounts",
					parameters: {
						shop: "uk",
					},
				},
				submit_payment: {
					name: "grp.models.order.payment.store",
					parameters: {
						order: 1,
						customer: 1,
					},
				},
			},
			total_amount: 91.88,
			paid_amount: 0,
			pay_amount: 91.88,
		},
		estimated_weight: 0,
	},
	order_summary: [
		[
			{
				label: "Items",
				quantity: 0,
				price_base: "Multiple",
				price_total: "78.20",
			},
		],
		[
			{
				label: "Charges",
				information: "",
				price_total: "0",
			},
			{
				label: "Shipping",
				information: "",
				price_total: "0",
			},
		],
		[
			{
				label: "Net",
				information: "",
				price_total: "78.20",
			},
			{
				label: "Tax 20%",
				information: "",
				price_total: "13.68",
			},
		],
		[
			{
				label: "Total",
				price_total: "91.88",
			},
		],
	],
	currency: {
		id: 23,
		code: "GBP",
		name: "British Pound",
		symbol: "£",
	},
})

const currency = ref({
	data: {
		id: 23,
		code: "GBP",
		name: "British Pound",
		symbol: "£",
	},
})

const data = ref({
	data: {
		id: 1,
		reference: "6900",
		state: "dispatched",
		state_label: "Dispatched",
		state_icon: {
			tooltip: "Dispatched",
			icon: "fal fa-shipping-fast",
			class: "text-gray-500",
			color: "purple",
			app: {
				name: "check-double",
				type: "font-awesome-5",
			},
		},
	},
})

const timelines = ref({
	creating: {
		label: "Creating",
		tooltip: "Creating",
		key: "creating",
		timestamp: "2003-08-28T01:30:00.000000Z",
	},
	submitted: {
		label: "Submitted",
		tooltip: "Submitted",
		key: "submitted",
		timestamp: null,
	},
	in_warehouse: {
		label: "In Warehouse",
		tooltip: "In Warehouse",
		key: "in_warehouse",
		timestamp: "2003-08-28 09:30:00+08",
	},
	handling: {
		label: "Handling",
		tooltip: "Handling",
		key: "handling",
		timestamp: null,
	},
	packed: {
		label: "Packed",
		tooltip: "Packed",
		key: "packed",
		timestamp: null,
	},
	finalised: {
		label: "Finalized",
		tooltip: "Finalized",
		key: "finalised",
		timestamp: "2003-08-28 09:30:00+08",
	},
	dispatched: {
		label: "Dispatched",
		tooltip: "Dispatched",
		key: "dispatched",
		timestamp: "2003-08-28 17:30:00+08",
	},
})
const isModalOpen = ref(false)
const notes = ref("hahaha")
const noteModalValue = ref(notes)
const fallbackBgColor = "#f9fafb" // Background
const fallbackColor = "#374151"
</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead">
		<template #otherBefore>
			<!-- Section: Add notes -->
			<Popover v-if="!notes?.note_list?.some((item) => !!item?.note?.trim())">
				<template #button="{ open }">
					<Button icon="fal fa-sticky-note" type="tertiary" label="Add notes" />
				</template>
				<template #content="{ close: closed }">
					<div class="w-[350px]">
						<span class="text-xs px-1 my-2">{{ trans("Select type note") }}: </span>
						<div class="">
							<PureMultiselect
								v-model="noteToSubmit.selectedNote"
								@update:modelValue="() => (errorNote = '')"
								:placeholder="trans('Select type note')"
								required
								:options="[
									{ label: 'Public note', value: 'public_notes' },
									{ label: 'Private note', value: 'internal_notes' },
								]"
								valueProp="value" />

							<!-- <p v-if="get(formAddService, ['errors', 'service_id'])" class="mt-2 text-sm text-red-500">
                                {{ formAddService.errors.service_id }}
                            </p> -->
						</div>

						<div class="mt-3">
							<span class="text-xs px-1 my-2">{{ trans("Note") }}: </span>
							<PureTextarea
								v-model="noteToSubmit.value"
								:placeholder="trans('Note')"
								@keydown.enter="() => onSubmitNote(closed)" />
						</div>

						<p v-if="errorNote" class="mt-2 text-sm text-red-600">*{{ errorNote }}</p>

						<div class="flex justify-end mt-3">
							<Button
								@click="() => onSubmitNote(closed)"
								:style="'save'"
								:loading="isLoadingButton === 'submitNote'"
								:disabled="!noteToSubmit.value"
								label="Save"
								full />
						</div>

						<!-- Loading: fetching service list -->
						<div
							v-if="isLoadingButton === 'submitNote'"
							class="bg-white/50 absolute inset-0 flex place-content-center items-center">
							<FontAwesomeIcon
								icon="fad fa-spinner-third"
								class="animate-spin text-5xl"
								fixed-width
								aria-hidden="true" />
						</div>
					</div>
				</template>
			</Popover>
		</template>
		<template #other>
			<Button
				v-if="currentTab === 'attachments'"
				@click="() => (isModalUploadOpen = true)"
				label="Attach"
				icon="upload" />
		</template>
	</PageHeading>

	<!-- Section: Pallet Warning -->
	<div v-if="alert?.status" class="p-2 pb-0">
		<AlertMessage :alert />
	</div>

	<!-- Section: Timeline -->
	<div
		v-if="data?.data?.state != 'in-process'"
		class="mt-4 sm:mt-0 border-b border-gray-200 pb-2">
		<Timeline
			v-if="timelines"
			:options="timelines"
			:state="data?.data?.state"
			:slidesPerView="6" />
	</div>

	<div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-gray-300 border-b border-gray-200">
		<BoxStatPallet class="py-2 px-3" icon="fal fa-user">
			<!-- Field: Reference Number -->
			<Link
				as="a"
				v-if="box_stats?.customer.reference"
				v-tooltip="trans('Reference')"
				:href="'route(box_stats?.customer.route.name, box_stats?.customer.route.parameters)'"
				class="pl-1 flex items-center w-fit flex-none gap-x-2 cursor-pointer primaryLink">
				<dt class="flex-none">
					<FontAwesomeIcon
						icon="fal fa-user"
						class="text-gray-400"
						fixed-width
						aria-hidden="true" />
				</dt>
				<dd class="text-sm text-gray-500">#{{ box_stats?.customer.reference }}</dd>
			</Link>

			<!-- Field: Contact name -->
			<div
				v-if="box_stats?.customer.contact_name"
				v-tooltip="trans('Contact name')"
				class="pl-1 flex items-center w-full flex-none gap-x-2">
				<dt class="flex-none">
					<FontAwesomeIcon
						icon="fal fa-id-card-alt"
						class="text-gray-400"
						fixed-width
						aria-hidden="true" />
				</dt>
				<dd class="text-sm text-gray-500">{{ box_stats?.customer.contact_name }}</dd>
			</div>

			<!-- Field: Company name -->
			<div
				v-if="box_stats?.customer.company_name"
				v-tooltip="trans('Company name')"
				class="pl-1 flex items-center w-full flex-none gap-x-2">
				<dt class="flex-none">
					<FontAwesomeIcon
						icon="fal fa-building"
						class="text-gray-400"
						fixed-width
						aria-hidden="true" />
				</dt>
				<dd class="text-sm text-gray-500">{{ box_stats?.customer.company_name }}</dd>
			</div>

			<!-- Field: Email -->
			<div
				v-if="box_stats?.customer.email"
				class="pl-1 flex items-center w-full flex-none gap-x-2">
				<dt v-tooltip="trans('Email')" class="flex-none">
					<FontAwesomeIcon
						icon="fal fa-envelope"
						class="text-gray-400"
						fixed-width
						aria-hidden="true" />
				</dt>
				<a
					:href="`mailto:${box_stats?.customer.email}`"
					v-tooltip="'Click to send email'"
					class="text-sm text-gray-500 hover:text-gray-700 truncate"
					>{{ box_stats?.customer.email }}</a
				>
			</div>

			<!-- Field: Phone -->
			<div
				v-if="box_stats?.customer.phone"
				class="pl-1 flex items-center w-full flex-none gap-x-2">
				<dt v-tooltip="trans('Phone')" class="flex-none">
					<FontAwesomeIcon
						icon="fal fa-phone"
						class="text-gray-400"
						fixed-width
						aria-hidden="true" />
				</dt>
				<a
					:href="`tel:${box_stats?.customer.phone}`"
					v-tooltip="'Click to make a phone call'"
					class="text-sm text-gray-500 hover:text-gray-700"
					>{{ box_stats?.customer.phone }}</a
				>
			</div>

			<!-- Field: Billing Address -->
			<div
				v-if="box_stats?.customer?.addresses?.billing?.formatted_address"
				class="pl-1 flex items w-full flex-none gap-x-2"
				v-tooltip="trans('Billing address')">
				<dt class="flex-none">
					<FontAwesomeIcon
						icon="fal fa-dollar-sign"
						class="text-gray-400"
						fixed-width
						aria-hidden="true" />
				</dt>
				<dd
					class="w-full text-gray-500 text-xs relative px-2.5 py-2 ring-1 ring-gray-300 rounded bg-gray-50"
					v-html="box_stats?.customer.addresses.billing.formatted_address"></dd>
			</div>

			<!-- Field: Shipping Address -->
			<div
				v-if="box_stats?.customer?.addresses?.delivery?.formatted_address"
				class="mt-2 pl-1 flex items w-full flex-none gap-x-2"
				v-tooltip="trans('Shipping address')">
				<dt class="flex-none">
					<FontAwesomeIcon
						icon="fal fa-shipping-fast"
						class="text-gray-400"
						fixed-width
						aria-hidden="true" />
				</dt>
				<dd
					class="w-full text-gray-500 text-xs relative px-2.5 py-2 ring-1 ring-gray-300 rounded bg-gray-50"
					v-html="box_stats?.customer.addresses.delivery.formatted_address"></dd>
			</div>
		</BoxStatPallet>

		<!-- Box: Product stats -->
		<BoxStatPallet class="py-4 pl-1.5 pr-3" icon="fal fa-user">
			<div class="relative flex items-start w-full flex-none gap-x-1">
				<dt class="flex-none pt-0.5">
					<FontAwesomeIcon
						icon="fal fa-dollar-sign"
						fixed-width
						aria-hidden="true"
						class="text-gray-500" />
				</dt>

				<NeedToPay
					@click="
						() =>
							box_stats.products.payment.pay_amount > 0
								? ((isOpenModalPayment = true), fetchPaymentMethod())
								: false
					"
					:totalAmount="box_stats.products.payment.total_amount"
					:paidAmount="box_stats.products.payment.paid_amount"
					:payAmount="box_stats.products.payment.pay_amount"
					:class="[
						box_stats.products.payment.pay_amount
							? 'hover:bg-gray-100 cursor-pointer'
							: '',
					]"
					:currencyCode="currency.code" />
			</div>

			<div class="mt-1 flex items-center w-full flex-none gap-x-1.5">
				<dt class="flex-none">
					<FontAwesomeIcon
						icon="fal fa-weight"
						fixed-width
						aria-hidden="true"
						class="text-gray-500" />
				</dt>
				<dd class="text-gray-500 sep" v-tooltip="trans('Estimated weight of all products')">
					{{ box_stats?.products.estimated_weight || 0 }} kilograms
				</dd>
			</div>
			<div class="relative flex items-start w-full gap-x-1">
				<dt class="flex-none pt-0.5">
					<FontAwesomeIcon
						icon="fal fa-sticky-note"
						fixed-width
						aria-hidden="true"
						class="text-gray-500" />
				</dt>

				<!-- Section: Note -->
				<div
					class="relative h-full flex flex-col items-center w-full p-4 bg-white rounded-lg border border-gray-200"
					:style="{
						backgroundColor: fallbackBgColor,
						color: fallbackColor,
					}">
					<!-- Edit Icon in Corner -->
					<div
						v-if="notes"
						@click="isModalOpen = true"
						v-tooltip="trans('Edit note')"
						class="absolute top-2 right-2 group cursor-pointer w-fit h-5 flex items-center">
						<FontAwesomeIcon
							icon="fas fa-pencil"
							size="xs"
							class="group-hover:text-gray-600 text-gray-500"
							fixed-width
							aria-hidden="true" />
					</div>

					<!-- Note Text -->
					<p
						class="text-xs md:text-sm break-words w-full"
						:style="{
							color: fallbackColor,
						}">
						<template v-if="notes">{{ notes }}</template>
						<span
							v-else
							class="italic opacity-75 animate-pulse"
							:style="{
								color: fallbackColor + '55',
							}">
							{{ trans("No note added") }}
						</span>
					</p>
				</div>
			</div>
		</BoxStatPallet>

		<!-- Box: Order summary -->
		<BoxStatPallet class="col-span-2 border-t lg:border-t-0 border-gray-300">
			<section aria-labelledby="summary-heading" class="rounded-lg px-4 py-4 sm:px-6 lg:mt-0">
				<!-- <h2 id="summary-heading" class="text-lg font-medium">Order summary</h2> -->

				<OrderSummary :order_summary="box_stats.order_summary" />

				<!-- <div class="mt-6">
                    <button type="submit"
                        class="w-full rounded-md border border-transparent bg-indigo-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Checkout</button>
                </div> -->
			</section>
		</BoxStatPallet>
	</div>

	<Tabs :current="currentTab" :navigation="tabs?.navigation" @update:tab="handleTabUpdate" />

	<div class="pb-12">
		<component
			:is="component"
			:data="props[currentTab as keyof typeof props]"
			:tab="currentTab"
			:updateRoute="routes.updateOrderRoute"
			:state="data?.data?.state"
			:detachRoute="attachmentRoutes?.detachRoute" />
	</div>

	<UploadAttachment
		v-model="isModalUploadOpen"
		scope="attachment"
		:title="{
			label: 'Upload your file',
			information: 'The list of column file: customer_reference, notes, stored_items',
		}"
		progressDescription="Adding Pallet Deliveries"
		:attachmentRoutes="attachmentRoutes" />
		
		<Modal :isOpen="isModalOpen" @onClose="() => (isModalOpen = false, noteModalValue = notes)">
		<div class="min-h-72 max-h-96 px-2 overflow-auto">
            <div class="text-xl font-semibold mb-2">{{ notes }}'s note</div>
			<div class="relative isolate">
                <div v-if="noteModalValue" @click="() => noteModalValue = ''" class="z-10 absolute top-1 right-1 text-red-400 hover:text-red-600 text-xxs cursor-pointer">
                    Clear
                </div>
                <PureTextarea v-model="noteModalValue" counter :rows="6" maxLength="5000" />
            </div>

            <div class="flex justify-end gap-x-2 mt-3">
                <Button label="cancel" @click="() => (isModalOpen = false, noteModalValue = notes)" :style="'tertiary'" />
                <Button label="Save"  :loading="isSubmitNoteLoading" :disabled="noteModalValue == notes" />
            </div>
		</div>
	</Modal>
</template>
