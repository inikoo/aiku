<script setup lang="ts">
import { computed, inject, ref, watch } from "vue"
import { router } from "@inertiajs/vue3"
import axios from "axios"
import { debounce } from "lodash-es"
import Dialog from "primevue/dialog"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import { notify } from "@kyvg/vue3-notification"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheck, faTimes, faSearch } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"
import ServiceSelectOption from "@/Components/Fulfilment/ServiceSelectOption.vue"
import { ctrans } from "@/Composables/useTrans"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import { routeType } from "@/types/route"

library.add(faCheck, faTimes, faSearch)

interface TransactionOption {
	id: number
	historic_asset_id: number
	code?: string
	name: string
	price: number
	unit: string
	currency_code: string
	is_pinned?: boolean
	is_pallet_handling?: boolean
}

const props = defineProps<{
	title: string
	fetchRoute: routeType
	submitRoute: routeType
	isService?: boolean
}>()

const emits = defineEmits<{
	(e: "success"): void
}>()

const isOpen = defineModel<boolean>({ default: false })

const locale = inject("locale", aikuLocaleStructure)

const searchQuery = ref("")
const options = ref<TransactionOption[]>([])
const nextPageUrl = ref<string | null>(null)
const isFetching = ref(false)
const isSubmitting = ref(false)
const quantities = ref<Record<number, number | null>>({})
const selectedOptions = ref<Record<number, TransactionOption>>({})

const selectedTransactions = computed(() =>
	Object.values(selectedOptions.value).filter((option) => (quantities.value[option.historic_asset_id] ?? 0) > 0)
)

const fetchOptions = async (url: string, isNextPage = false) => {
	isFetching.value = true
	try {
		const response = await axios.get(url)
		options.value = isNextPage ? [...options.value, ...(response.data?.data ?? [])] : response.data?.data ?? []
		nextPageUrl.value = response.data?.links?.next ?? null
	} catch (error) {
		notify({
			title: ctrans("Something went wrong."),
			text: ctrans("Failed to load the list, please try again."),
			type: "error",
		})
	} finally {
		isFetching.value = false
	}
}

const fetchFirstPage = () => {
	fetchOptions(
		route(props.fetchRoute.name, {
			...props.fetchRoute.parameters,
			"filter[global]": searchQuery.value,
		})
	)
}

const onSearch = debounce(fetchFirstPage, 300)

const onScrollList = (event: Event) => {
	const list = event.target as HTMLElement
	if (nextPageUrl.value && !isFetching.value && list.scrollTop + list.clientHeight >= list.scrollHeight - 40) {
		fetchOptions(nextPageUrl.value, true)
	}
}

const onUpdateQuantity = (option: TransactionOption, quantity: number | null) => {
	quantities.value[option.historic_asset_id] = quantity
	if (quantity && quantity > 0) {
		selectedOptions.value[option.historic_asset_id] = option
	} else {
		delete selectedOptions.value[option.historic_asset_id]
	}
}

const resetModal = () => {
	searchQuery.value = ""
	options.value = []
	nextPageUrl.value = null
	quantities.value = {}
	selectedOptions.value = {}
}

watch(isOpen, (value) => {
	if (value) {
		fetchFirstPage()
	} else {
		resetModal()
	}
}, { immediate: true })

const onSubmit = () => {
	router.post(
		route(props.submitRoute.name, props.submitRoute.parameters),
		{
			transactions: selectedTransactions.value.map((option) => ({
				historic_asset_id: option.historic_asset_id,
				quantity: quantities.value[option.historic_asset_id],
			})),
		},
		{
			preserveScroll: true,
			onStart: () => {
				isSubmitting.value = true
			},
			onSuccess: () => {
				isOpen.value = false
				emits("success")
			},
			onError: (errors) => {
				notify({
					title: ctrans("Something went wrong."),
					text: Object.values(errors)[0] ?? ctrans("Failed to add, please try again."),
					type: "error",
				})
			},
			onFinish: () => {
				isSubmitting.value = false
			},
		}
	)
}
</script>

<template>
	<Dialog
		v-model:visible="isOpen"
		modal
		:header="title"
		:draggable="false"
		:style="{ width: '44rem', maxWidth: 'calc(100vw - 2rem)' }"
		:pt="{ content: { class: '!pb-0' } }">
		<div class="flex flex-col gap-3">
			<div class="relative">
				<FontAwesomeIcon
					icon="fal fa-search"
					class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
					fixed-width
					aria-hidden="true" />
				<InputText
					v-model="searchQuery"
					:placeholder="ctrans('Search')"
					class="w-full !pl-9"
					autofocus
					@input="onSearch" />
			</div>

			<div v-if="selectedTransactions.length" class="flex flex-wrap gap-1.5">
				<span
					v-for="option in selectedTransactions"
					:key="option.historic_asset_id"
					class="inline-flex items-center gap-1 rounded border border-[--app-accent-muted] bg-[--app-accent-soft] px-2 py-0.5 text-xs">
					{{ option.name }} × {{ quantities[option.historic_asset_id] }}
					<button
						type="button"
						v-tooltip="ctrans('Remove')"
						class="text-gray-400 hover:text-gray-600"
						@click="onUpdateQuantity(option, null)">
						<FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
					</button>
				</span>
			</div>

			<div class="max-h-[50vh] overflow-y-auto rounded border border-gray-200" @scroll="onScrollList">
				<div
					v-for="option in options"
					:key="option.historic_asset_id"
					class="flex items-center gap-3 border-b border-gray-100 px-3 py-2 last:border-b-0 hover:bg-gray-50"
					:class="(quantities[option.historic_asset_id] ?? 0) > 0 ? 'bg-[--app-accent-soft]' : ''">
					<div class="min-w-0 grow">
						<ServiceSelectOption v-if="isService" :option="option" />
						<template v-else>
							{{ option.name }}
							<span class="text-sm text-gray-400">
								({{ locale.currencyFormat(option.currency_code, option.price) }}/{{ option.unit }})
							</span>
						</template>
					</div>
					<InputNumber
						:modelValue="quantities[option.historic_asset_id] ?? null"
						:min="0"
						:maxFractionDigits="3"
						:placeholder="ctrans('Qty')"
						:inputClass="'!w-24 !py-1 text-right'"
						@update:modelValue="(quantity: number | null) => onUpdateQuantity(option, quantity)" />
				</div>

				<div v-if="isFetching" class="flex justify-center py-3 text-gray-400">
					<LoadingIcon />
				</div>
				<div v-else-if="!options.length" class="py-6 text-center text-sm text-gray-400">
					{{ ctrans("No results found") }}
				</div>
			</div>
		</div>

		<template #footer>
			<div class="flex w-full items-center justify-between gap-2 pt-3">
				<span class="text-sm text-gray-500">
					{{ ctrans(":count selected", { count: String(selectedTransactions.length) }) }}
				</span>
				<div class="flex gap-2">
					<Button :label="ctrans('Cancel')" type="tertiary" @click="isOpen = false">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button
						:label="ctrans('Add')"
						type="tertiary"
						:loading="isSubmitting"
						:disabled="!selectedTransactions.length"
						@click="onSubmit">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
