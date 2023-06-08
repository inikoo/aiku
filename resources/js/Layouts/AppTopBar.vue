<template>
	<div class="flex justify-between items-center">
		<div class="block pb-3 pl-3 xl:w-56">
			<img class="h-4 mt-4 xl:h-6" src="/art/logo-color-trimmed.png" alt="Aiku" />
			<span class="font-logo mb-1 mr-2 xl:hidden whitespace-nowrap text-sm">
				{{ props.tenantName }}
			</span>
		</div>

		<!-- Left Menu -->
		<div class="flex items-center divide-x divide-gray-100 justify-center overflow-hidden">
			<Link
				v-if=" currentUrl && layout.navigation[currentUrl]?.topMenu && layout.navigation[currentUrl]?.topMenu?.subSections "
				v-for="menu in layout.navigation[currentUrl].topMenu.subSections"
				:href="route(menu.route.name)"
				class="group flex justify-center items-center cursor-pointer space-x-1 px-4">
				<FontAwesomeIcon
					:icon="menu.icon"
					class="h-3.5 w-3.5 pr-1 text-gray-600 group-hover:opacity-100 opacity-30 transition duration-100 ease-in-out"
					aria-hidden="true" />
				<span class="text-gray-600 capitalize">{{ menu.label }}</span>
			</Link>
		</div>

		<!-- Dropdown -->
		<div v-if=" currentUrl && layout.navigation[currentUrl].topMenu && layout.navigation[currentUrl].topMenu.dropdown && layout.navigation[currentUrl]?.topMenu?.dropdown.options.data.length > 1">
			<TopBarDropdown :currentPage="currentUrl" />
		</div>

		<!-- Right Menu -->
		<div
			class="text-gray-600 inline-flex place-self-center rounded-r justify-center text-sm ring-1 ring-inset ring-gray-300">
			<!-- The href is if initial state then the menu will show all shop, but if data shop only 1 data then menu is directly to that 1 data -->
			<Link
				v-if="
					currentUrl &&
					layout.navigation[currentUrl].topMenu &&
					layout.navigation[currentUrl].topMenu.dropdown?.subsections
				"
				v-for="menu in layout.navigation[currentUrl].topMenu.dropdown.subsections"
				:href=" layout[currentUrl].currentData.slug ?
						route(menu.route?.selected, layout[currentUrl].currentData.slug) :
						layout.navigation[currentUrl]?.topMenu?.dropdown.options.data.length == 1 ?
							route(menu.route?.selected, layout.navigation[currentUrl]?.topMenu?.dropdown.options.data[0].slug) :
							route(menu.route?.all)"
				:title="menu.tooltip"
				class="group flex justify-center items-center cursor-pointer py-1 space-x-1 px-4 hover:font-semibold hover:text-indigo-600">
				<FontAwesomeIcon
					:icon="menu.icon"
					class="h-3.5 w-3.5 pr-1 group-hover:opacity-100 opacity-30 transition duration-100 ease-in-out"
					aria-hidden="true" />
				<span class="capitalize">{{ menu.label }}</span>
			</Link>
		</div>
	</div>
</template>

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ref } from "vue"
import { router } from "@inertiajs/vue3"
import TopBarDropdown from "@/Components/Navigation/TopBarDropdown.vue"
import { useLayoutStore } from "@/Stores/layout"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import {
	faNetworkWired,
	faTerminal,
	faCalendar,
	faStopwatch,
	faChessClock,
	faUserAlien,
	faCog,
	faBox,
	faBoxesAlt,
	faWarehouse,
	faMapSigns,
	faBrowser,
} from "@/../private/pro-light-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"

library.add(
	faNetworkWired,
	faTerminal,
	faCalendar,
	faStopwatch,
	faChessClock,
	faUserAlien,
	faCog,
	faBox,
	faBoxesAlt,
	faWarehouse,
	faMapSigns,
	faBrowser
)
const layout = useLayoutStore()

const props = defineProps<{
	tenantName: string
}>()

const currentUrl = ref()
router.on("navigate", (event) => {
	currentUrl.value = event.detail.page.url.split("/")[1]
	console.log(layout.navigation[currentUrl.value]?.topMenu?.dropdown.options.data[0].slug)
})
</script>

<style scoped></style>
