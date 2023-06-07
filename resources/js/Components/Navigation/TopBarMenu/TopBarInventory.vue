<template>
	<div class="flex gap-x-3">

		<!-- Left Menu -->
		<div class="flex divide-x divide-gray-200">
			<Link
				v-for="topMenu in layout.navigation.inventory.topMenu"
				:href="route(topMenu.route.name)"
				class="group space-x-1 flex items-center px-3">
				<FontAwesomeIcon
					:icon="topMenu.icon"
					class="h-4 w-4 p-1 text-gray-800 group-hover:bg-indigo-600 group-hover:text-white rounded opacity-60 group-hover:opacity-100"
					aria-hidden="true" />
				<span class="text-gray-800 capitalize">{{ topMenu.label }}</span>
			</Link>
		</div>

		<!-- Dropdown -->
		<div class="grid">
			<AppShopNavigationDropDown class="place-self-center" />
		</div>

		<!-- Right Menu -->
		<div class="flex items-center divide-x divide-gray-200 justify-center rounded overflow-hidden">
			<div v-for="menu in rightMenu" class="group flex justify-center items-center cursor-pointer space-x-1 px-3">
				<FontAwesomeIcon
					:icon="menu.icon"
					class="h-4 w-4 p-1 text-gray-800 group-hover:bg-indigo-600 group-hover:text-white rounded opacity-60 group-hover:opacity-100"
					aria-hidden="true" />
				<span class="text-gray-800 capitalize">{{menu.label}}</span>
			</div>
		</div>
	</div>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3"
import { useLayoutStore } from "@/Stores/layout"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import {
	faNetworkWired,
	faTerminal,
	faCalendar,
	faStopwatch,
	faChessClock,
} from "../../../../private/pro-light-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"
import AppShopNavigationDropDown from "@/Layouts/AppShopNavigationDropDown.vue"

library.add(faNetworkWired, faTerminal, faCalendar, faStopwatch, faChessClock)
const layout = useLayoutStore()

const rightMenu = [
	{
		'label': 'Warehouse',
		'icon': ['fal', 'warehouse'],
	},
	{
		'label': 'Warehouse Areas',
		'icon': ['fal', 'map-signs'],
	},
	{
		'label': 'Locations',
		'icon': ['fal', 'inventory'],
	},
]
</script>

<style lang="scss" scoped></style>
