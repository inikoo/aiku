<script setup lang="ts">
import { inject, ref } from "vue"
import { router } from "@inertiajs/vue3"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"
import ToggleSwitch from "primevue/toggleswitch"
import { get } from "lodash"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

const props = defineProps<{
	intervalOptions: {
		label: string
		value: string
	}[]
	settings: {
		db_settings: {
			selected_interval?: string
			selected_currency_in_grp?: string
			selected_currency_in_org?: string
			selected_shop_open?: string
			selected_shop_closed?: string
		}
		key_currency?: string
		key_shop?: string
		options_currency: {
			value: string
			label: string
		}[]
		options_shop: {
			value: string
			label: string
		}[]
	}
	tableType?: string
}>()

const layout = inject("layout", layoutStructure)
const isSectionVisible = ref(false)

// Section: Interval
const isLoadingInterval = ref<string | null>(null)
const updateInterval = (interval_code: string) => {
	router.patch(
		route("grp.models.user.update", layout.user?.id),
		{
			settings: {
				selected_interval: interval_code,
			},
		},
		{
			onStart: () => {
				isLoadingInterval.value = interval_code
			},
			onFinish: () => {
				isLoadingInterval.value = null
			},
			preserveScroll: true,
		}
	)
}

// Section: Currency
const isLoadingCurrency = ref<boolean>(false)
const updateCurrency = (currency_scope: string) => {
	router.patch(
		route("grp.models.user.update", layout.user?.id),
		{
			settings: {
				[`selected_currency_in_${props.settings?.key_currency || "grp"}`]: currency_scope,
			},
		},
		{
			onStart: () => {
				isLoadingCurrency.value = true
			},
			onFinish: () => {
				isLoadingCurrency.value = false
			},
			preserveScroll: true,
		}
	)
}

const updateShop = (shop_scope: string) => {
	router.patch(
		route("grp.models.user.update", layout.user?.id),
		{
			settings: {
				[`selected_shop_open`]: shop_scope,
			},
		},
		{
			onStart: () => {
				isLoadingCurrency.value = true
			},
			onFinish: () => {
				isLoadingCurrency.value = false
			},
			preserveScroll: true,
		}
	)
}

const updateAmountFormat = (amountFormat: string) => {
	console.log(amountFormat, "haha")

	router.patch(
		route("grp.models.user.update", layout.user?.id),
		{
			settings: {
				[`selected_amount`]: amountFormat,
			},
		},
		{
			onStart: () => {
				isLoadingCurrency.value = true
			},
			onFinish: () => {
				isLoadingCurrency.value = false
			},
			preserveScroll: true,
		}
	)
}
</script>

<template>
	<div class="relative">
		<div class="absolute bottom-0 right-0 p-2">
			<FontAwesomeIcon
				icon="fal fa-cog"
				fixed-width
				aria-hidden="true"
				@click="isSectionVisible = !isSectionVisible"
				class="text-2xl text-indigo-500 cursor-pointer hover:text-indigo-600 transition" />
		</div>
		<div class="pr-12 mb-2">
			<!-- Toggles Section (Shop and Currency) -->
			<div
				v-show="isSectionVisible"
				class="flex flex-wrap justify-between items-center gap-4 lg:gap-8 mb-2">
				<!-- Shop Toggle (for tableType === 'org') -->
				<div v-if="tableType === 'org'" class="flex items-center space-x-4">
					<p
						class="font-medium"
						:class="[
							settings.db_settings.selected_shop_open ===
							settings.options_shop[1].value
								? 'text-black font-bold'
								: 'opacity-50',
						]">
						{{ settings.options_shop[1].label }}
					</p>

					<!-- Shop Toggle Switch -->
					<ToggleSwitch
						:modelValue="settings.db_settings.selected_shop_open === 'open'"
						@update:modelValue="(value) => updateShop(value ? 'open' : 'closed')"
						:trueValue="'true'"
						:falseValue="'false'" />

					<p
						class="font-medium"
						:class="[
							settings.db_settings.selected_shop_open ===
							settings.options_shop[0].value
								? 'text-black font-bold'
								: 'opacity-50',
						]">
						{{ settings.options_shop[0].label }}
					</p>
				</div>
				<div v-else></div>

				<div class="flex items-center justify-end space-x-4">
					<p
						class="font-medium"
						:class="[
							settings.selected_amount === false
								? ''
								: 'opacity-50',
						]">
						Minified
					</p>
					<ToggleSwitch
						:modelValue="settings.selected_amount"
						@update:modelValue="(e: string) => updateAmountFormat(e)"
						class="mx-2"
						:disabled="isLoadingCurrency"
						v-tooltip="'amount format'" />

					<p
						class="font-medium"
						:class="[
							settings.selected_amount ===
							true
								? ''
								: 'opacity-50',
						]">
						Full
					</p>
					<p
						class="font-medium"
						:class="[
							settings.options_currency[0].value ===
							get(
								settings,
								['db_settings', `selected_currency_in_${settings.key_currency}`],
								''
							)
								? ''
								: 'opacity-50',
						]">
						{{ settings.options_currency[0].label }}
					</p>

					<ToggleSwitch
						:modelValue="
							get(
								settings,
								['db_settings', `selected_currency_in_${settings.key_currency}`],
								''
							)
						"
						@update:modelValue="(e: string) => updateCurrency(e)"
						class="mx-2"
						:disabled="isLoadingCurrency"
						:trueValue="settings.options_currency[1].value"
						:falseValue="settings.options_currency[0].value"
						v-tooltip="'currency'" />

					<p
						class="font-medium"
						:class="[
							settings.options_currency[1].value ===
							get(
								settings,
								['db_settings', `selected_currency_in_${settings.key_currency}`],
								''
							)
								? ''
								: 'opacity-50',
						]">
						{{ settings.options_currency[1].label }}
					</p>
				</div>
			</div>

			<nav class="isolate flex rounded-full border p-1 hidden sm:flex" aria-label="Tabs">
				<div class="flex flex-1">
					<div
						v-for="(interval, idxInterval) in intervalOptions"
						:key="idxInterval"
						@click="updateInterval(interval.value)"
						:class="[
							interval.value === settings.db_settings.selected_interval
								? 'bg-indigo-500 text-white font-medium'
								: 'text-gray-500 hover:text-gray-700 hover:bg-gray-100',
						]"
						v-tooltip="interval.label"
						class="relative flex-1 rounded-full py-2 px-4 text-center text-sm cursor-pointer select-none transition duration-200">
						<span :class="isLoadingInterval === interval.value ? 'opacity-0' : ''">
							{{ interval.value }}
						</span>
						<span
							class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2"
							:class="isLoadingInterval === interval.value ? '' : 'opacity-0'">
							<LoadingIcon />
						</span>
					</div>
				</div>
			</nav>

			<!-- Mobile: Interval Dropdown -->
			<div class="block sm:hidden">
				<label for="intervalDropdown" class="sr-only">Select Interval</label>
				<select
					id="intervalDropdown"
					class="w-full border-gray-300 rounded-md text-sm"
					v-model="settings.db_settings.selected_interval"
					@change="updateInterval($event.target.value)">
					<option
						v-for="(interval, idxInterval) in intervalOptions"
						:key="idxInterval"
						:value="interval.value">
						{{ interval.value }}
					</option>
				</select>
			</div>
		</div>
	</div>
</template>
