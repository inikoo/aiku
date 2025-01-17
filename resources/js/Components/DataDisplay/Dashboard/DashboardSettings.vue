<script setup lang="ts">
import { inject, ref } from "vue"
import { router } from "@inertiajs/vue3"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"
import ToggleSwitch from "primevue/toggleswitch"
import { get } from "lodash"

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
			selected_shop_open?: boolean
			selected_shop_closed?: boolean
		}
		key_currency?: string
		key_shop?: string
		options_currency: {
			value: string
			label: string
		}[]
	}
	checked?: boolean
	tableType?: string
}>()


const layout = inject('layout', layoutStructure)

// Section: Interval
const isLoadingInterval = ref<string | null>(null)
const updateInterval = (interval_code: string) => {
	router.patch(route("grp.models.user.update", layout.user?.id), {
		settings: {
			selected_interval: interval_code,
		},
	}, {
		onStart: () => {
			isLoadingInterval.value = interval_code
		},
		onFinish: () => {
			isLoadingInterval.value = null
		},
		preserveScroll: true,
	})
}

// Section: Currency
const isLoadingCurrency = ref<boolean>(false)
const updateCurrency = (currency_scope: string) => {
	router.patch(route("grp.models.user.update", layout.user?.id), {
		settings: {
			[`selected_currency_in_${props.settings?.key_currency || 'grp'}`]: currency_scope,
		},
	}, {
		onStart: () => {
			isLoadingCurrency.value = true
		},
		onFinish: () => {
			isLoadingCurrency.value = false
		},
		preserveScroll: true,
	})
}

const updateShop = (shop_scope: string) => {
	router.patch(route("grp.models.user.update", layout.user?.id), {
		settings: {
			[`selected_shop_in_${props.settings?.key_shop || 'true'}`]: shop_scope,
		},
	}, {
		onStart: () => {
			isLoadingCurrency.value = true
		},
		onFinish: () => {
			isLoadingCurrency.value = false
		},
		preserveScroll: true,
	})
}
</script>

<template>
	<div class="relative bg-gradient-to-r mb-2 from-white to-gray-50 p-6 rounded-xl shadow-md space-y-6 border border-gray-200">

		<!-- Section: Currency -->
		<div class="flex flex-wrap justify-end items-center space-x-2 lg:space-x-4">
		<!-- 	<ToggleSwitch 
			:modelValue="get(settings, ['db_settings', `selected_shop_in_${settings.key_shop}`], '')" 
			@update:modelValue="(e: string) => updateShop(e)"
			:trueValue="settings.options_currency[1].value"
			:falseValue="settings.options_currency[0].value"
			/> -->

			<p class="font-medium" :class="[settings.options_currency[0].value == get(settings, ['db_settings', `selected_currency_in_${settings.key_currency}`], '') ? '' : 'opacity-50']">
				{{ settings.options_currency[0].label }}
			</p>

			<ToggleSwitch
				:modelValue="get(settings, ['db_settings', `selected_currency_in_${settings.key_currency}`], '')"
				@update:modelValue="(e: string) => updateCurrency(e)"
				class="mx-2"
				:disabled="isLoadingCurrency"
				:trueValue="settings.options_currency[1].value"
				:falseValue="settings.options_currency[0].value"
			/>

			<p class="font-medium" :class="[settings.options_currency[1].value == get(settings, ['db_settings', `selected_currency_in_${settings.key_currency}`], '') ? '' : 'opacity-50']">
				{{ settings.options_currency[1].label }}
			</p>
		</div>

		<!-- Section: Interval -->
		<nav class="isolate flex rounded-full bg-white-50 border border-gray-200 p-1"
			aria-label="Tabs">
			<div
				v-for="(interval, idxInterval) in intervalOptions"
				:key="idxInterval"
				@click="updateInterval(interval.value)"
				:class="[
					interval.value === settings.db_settings.selected_interval
						? 'bg-indigo-500 text-white font-medium'
						: 'text-gray-500 hover:text-gray-700 hover:bg-gray-100',
				]"
				class="relative flex-1 rounded-full py-2 px-4 text-center text-sm cursor-pointer select-none transition duration-200">
				<span :class="isLoadingInterval == interval.value ? 'opacity-0' : ''">{{ interval.value }}</span>
				<span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2" :class="isLoadingInterval == interval.value ? '' : 'opacity-0'"><LoadingIcon /></span>
			</div>
		</nav>
	</div>
</template>
