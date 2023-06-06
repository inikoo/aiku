<template>
	<div class="flex gap-x-6">
		<Link
			v-for="topMenu in layout.navigation.inventory.topMenu"
			:href="route(topMenu.route.name)"
			class="group space-x-1 flex items-center">
			<FontAwesomeIcon
				:icon="topMenu.icon"
				class="h-4 w-4 border border-indigo-600 p-1 text-indigo-700 group-hover:bg-indigo-600 group-hover:text-white rounded"
				aria-hidden="true" />
			<span class="text-sm text-gray-700 capitalize">{{ topMenu.label }}</span>
		</Link>
		<div class="grid">
			<AppShopNavigationDropDown class="place-self-center" />
		</div>
        <div class="flex divide-x divide-gray-300 justify-center rounded overflow-hidden">
            <div class="px-2 cursor-pointer hover:bg-indigo-500 hover:text-white">Warehouse</div>
            <div class="px-2 cursor-pointer hover:bg-indigo-500 hover:text-white">Warehouse Areas</div>
            <div class="px-2 cursor-pointer hover:bg-indigo-500 hover:text-white">Locations</div>
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
</script>

<style lang="scss" scoped></style>
