<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ref } from "vue"
import { router } from "@inertiajs/vue3"
import TopBarMenu from "@/Components/Navigation/TopBarMenu.vue"
import { capitalize } from "@/Composables/capitalize"
import { useLayoutStore } from "@/Stores/layout.js"
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
	faPeopleArrows,
	faPersonDolly,
	faClipboardList,
	faFolder,
	faFolders,
	faBrowser,
	faBars,
    faBuilding,
    faCube
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
	faPeopleArrows,
	faPersonDolly,
	faClipboardList,
	faFolder,
	faFolders,
	faBrowser,
	faBars,
    faBuilding,
    faCube
)
const layout = useLayoutStore()

const props = defineProps<{
	tenantName: string
}>()

const currentUrl = ref()
const currentRoute = ref()
router.on("navigate", (event) => {
	currentRoute.value = route().current()
	currentUrl.value = event.detail.page.url.split("/")[1]
})

const generateLink = (menu) => {
	return layout?.navigation?.[currentUrl]?.currentData.slug  // If the slug is not null
		? route(menu.route?.selected, layout?.navigation?.[currentUrl]?.currentData.slug)  // Then the menu go to that slug
		: currentRoute != layout?.navigation?.[currentUrl]?.route  // Check if active route is same as 'All List' slug
			? route().params[Object.keys(route().params)[0]]  // Check if there is active parameter (for subpage)
				? route(menu.route?.selected, route().params)  // If parameter exist go to that slug
				: route(menu.route?.all)  // If parameter doesn't exist then Link is 'All list'
			: layout.navigation?.[currentUrl]?.topMenu?.dropdown.options.data.length == 1  // If list is only 1 data
				? route(menu.route?.selected, layout.navigation?.[currentUrl]?.topMenu?.dropdown.options.data[0]?.slug)  // Link go to that 1 data
				: route(menu.route?.all)  // If data is above than 1 data then Link to 'All list'
}
</script>

<template>
	<div class="flex flex-1 items-center justify-between lg:justify-start">
		<Link :href="route('dashboard.show')" class="md:pl-3 flex items-center h-full xl:overflow-hidden space-x-2 mr-6 xl:w-56 xl:pr-2 xl:border-r-2 xl:border-gray-200 xl:mr-0">
			<img class="h-7 md:h-5 shadow" src="/media/group/1" alt="Aiku" />
			<span class="hidden leading-none md:inline font-logo text-indigo-700 xl:truncate">
				{{ layout.tenant.name }}
			</span>
		</Link>

		<div class="flex">
			<!-- Left Menu -->
			<div class="text-sm flex items-center divide-x divide-gray-300 justify-center overflow-hidden">
				<Link
					v-if=" currentUrl && layout.navigation?.[currentUrl]?.topMenu && layout.navigation?.[currentUrl]?.topMenu?.subSections "
					v-for="menu in layout.navigation?.[currentUrl]?.topMenu.subSections"
					:href="route(menu.route.name)"
					class="group flex justify-end items-center cursor-pointer py-1 space-x-1 px-4 md:px-4 lg:px-4"
					:class="[currentRoute == menu.route.name ? 'text-indigo-600' : 'text-gray-600']"
					:title="capitalize(menu.label)"
				>
					<FontAwesomeIcon
						:icon="menu.icon"
						class="h-5 lg:h-3.5 w-auto group-hover:opacity-100 opacity-70 transition duration-100 ease-in-out"
						aria-hidden="true" />
					<span class="hidden lg:inline capitalize">{{ menu.label }}</span>
				</Link>
			</div>

			<!-- Dropdown -->
			<TopBarMenu v-if=" currentUrl && layout.navigation?.[currentUrl]?.topMenu && layout.navigation?.[currentUrl]?.topMenu.dropdown && layout.navigation?.[currentUrl]?.topMenu?.dropdown.options.data.length > 1" :currentPage="currentUrl" />
			
			<!-- Right Menu -->
			<div
				class="text-sm text-gray-600 inline-flex place-self-center rounded-r justify-center border-solid "
				:class="[layout.navigation?.[currentUrl]?.topMenu?.dropdown?.options?.data?.length > 1 ? 'border border-l-0 border-indigo-300' : 'border-l border-gray-300 divide-x divide-gray-300 ']"
			>
				<!-- href:
					If the slug is initial state (which is null) then the menu will show all shop,
					but if current route is contain params (slug of options) then the links is linkselected with that params (handle for refresh page that the state is back to null),
					if the 'show all shop' only contain 1 data then the links is directly to that 1 data
				-->
				<Link
					v-if="
						currentUrl &&
						layout.navigation?.[currentUrl]?.topMenu &&
						layout.navigation?.[currentUrl]?.topMenu.dropdown?.subsections"
					v-for="menu in layout.navigation?.[currentUrl]?.topMenu.dropdown.subsections"
					:href="generateLink(menu)"
					:title="capitalize(menu.tooltip)"
					class="group flex justify-center items-center cursor-pointer h-7 py-1 space-x-1 px-4"
					:class="[
						layout.navigation?.[currentUrl]?.topMenu?.dropdown?.options?.data?.length > 1 ? 'hover:text-indigo-600' : '',
						menu.route.all == 'inventory.warehouses.index' && !layout?.navigation?.[currentUrl]?.currentData.slug ? 'border-l-4 border-l-transparent' : '',
						route(currentRoute, route().params) == generateLink(menu) ? 'text-indigo-600' : 'text-gray-600'
					]"
				>
					<FontAwesomeIcon
						v-if="menu.route.all == 'inventory.warehouses.index' && !layout?.navigation?.[currentUrl]?.currentData.slug"
						icon="fal fa-bars"
						class="w-auto pr-1 group-hover:opacity-100 opacity-70 transition duration-100 ease-in-out"
						aria-hidden="true" />
					<FontAwesomeIcon
						v-else
						:icon="menu.icon"
						class="w-auto pr-1 group-hover:opacity-100 opacity-70 transition duration-100 ease-in-out"
						aria-hidden="true" />
						
					<p v-if="menu.route.selected != 'inventory.warehouses.show'" class="hidden lg:inline capitalize">
						<!-- To hide label for Warehouse route -->
						{{ menu.label }}
					</p>
				</Link>
			</div>
		</div>
	</div>
</template>

<style scoped></style>
