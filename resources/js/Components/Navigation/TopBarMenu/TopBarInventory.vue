<template>
	<div class="flex gap-x-3">

		<!-- Left Menu -->
		<div class="flex divide-x divide-gray-200">
			<Link
				v-for="topMenuLeft in layout.navigation.inventory.topMenuLeft"
				:href="route(topMenuLeft.route.name)"
				class="group space-x-1 flex items-center px-3">
				<FontAwesomeIcon
					:icon="topMenuLeft.icon"
					class="h-4 w-4 p-1 text-gray-800 group-hover:bg-indigo-600 group-hover:text-white rounded opacity-60 group-hover:opacity-100"
					aria-hidden="true" />
				<span class="text-gray-800 capitalize">{{ topMenuLeft.label }}</span>
			</Link>
		</div>

		<!-- Dropdown -->
		<div class="grid">
			<AppInventoryNavigationDropDown class="place-self-center" />
		</div>

		<!-- Right Menu -->
		<div class="flex items-center divide-x divide-gray-200 justify-center rounded overflow-hidden">
			<Link v-for="topMenuRight in layout.navigation.inventory.topMenuRight"
                  :href="route(topMenuRight.route.name)"
                  class="group space-x-1 flex items-center px-3">
				<FontAwesomeIcon
					:icon="topMenuRight.icon"
					class="h-4 w-4 p-1 text-gray-800 group-hover:bg-indigo-600 group-hover:text-white rounded opacity-60 group-hover:opacity-100"
					aria-hidden="true" />
				<span class="text-gray-800 capitalize">{{topMenuRight.label}}</span>
			</Link>
		</div>
	</div>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3"
import { useLayoutStore } from "@/Stores/layout"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt } from "../../../../private/pro-light-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"
import AppInventoryNavigationDropDown from "@/Layouts/AppInventoryNavigationDropDown.vue";

library.add(faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt)
const layout = useLayoutStore()

// const rightMenu = [
// 	{
// 		'label': 'Warehouse',
// 		'icon': ['fal', 'warehouse'],
// 	},
// 	{
// 		'label': 'Warehouse Areas',
// 		'icon': ['fal', 'map-signs'],
// 	},
// 	{
// 		'label': 'Locations',
// 		'icon': ['fal', 'inventory'],
// 	},
// ]
</script>

<style lang="scss" scoped></style>
