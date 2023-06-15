<template>
	<div class="flex flex-1 items-center justify-between lg:justify-start">
		<div class="md:pl-3 xl:pl-5 flex items-center h-full xl:w-56 space-x-2 pr-4">
			<img class="h-7 hidden sm:inline" src="/art/logo-color-trimmed.png" alt="Aiku" />
			<span class="font-logo hidden md:inline xl:hidden whitespace-nowrap text-xs">
				{{ props.tenantName }}
			</span>
		</div>

		<!-- Left Menu -->
		<div class="flex w-full">
			<div class="text-sm lg:text-base flex items-center divide-x divide-gray-100 min-w-max">
				<Link
					v-if=" currentUrl && layout.navigation?.[currentUrl]?.topMenu && layout.navigation?.[currentUrl]?.topMenu?.subSections "
					v-for="menu in layout.navigation?.[currentUrl]?.topMenu.subSections"
					:href="route(menu.route.name)"
					class="group flex justify-end items-center cursor-pointer py-1  lg:space-x-14 px-14 md:px-14 lg:px-14"
					:title="capitalize(menu.label)"
				>
					<FontAwesomeIcon
						:icon="menu.icon"
						class="h-5 lg:h-3.5 w-auto text-gray-600 group-hover:opacity-100 opacity-30 transition duration-100 ease-in-out"
						aria-hidden="true" />
					<span class="hidden lg:inline text-gray-600 capitalize">{{ menu.label }}</span>
				</Link>
			</div>

			<!-- Dropdown -->
			<div v-if=" currentUrl && layout.navigation?.[currentUrl]?.topMenu && layout.navigation?.[currentUrl]?.topMenu.dropdown && layout.navigation?.[currentUrl]?.topMenu?.dropdown.options.data.length > 1">
				<TopBarMenu :currentPage="currentUrl" />
			</div>
			
			<!-- Right Menu -->
			<div
				class="text-sm lg:text-base text-gray-600 inline-flex place-self-center rounded-r justify-center border-solid min-w-max"
				:class="[layout.navigation?.[currentUrl]?.topMenu?.dropdown?.options?.data?.length > 1 ? 'border border-l-0 border-indigo-300' : 'border-l border-gray-100 divide-x divide-gray-100 ']"
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
					:href="
						layout?.navigation?.[currentUrl]?.currentData.slug  // If the slug is not null
							? route(menu.route?.selected, layout?.navigation?.[currentUrl]?.currentData.slug)  // Then the menu go to that slug
							: currentRoute != layout?.navigation?.[currentUrl]?.route  // Check if active route is same as 'All List' slug
								? route().params[Object.keys(route().params)[0]]  // Check if there is active parameter (for subpage)
									? route(menu.route?.selected, route().params[Object.keys(route().params)[0]])  // If parameter exist go to that slug
									: route(menu.route?.all)  // If parameter doesn't exist then Link is 'All list'
								: layout.navigation?.[currentUrl]?.topMenu?.dropdown.options.data.length == 1  // If list is only 1 data
									? route(menu.route?.selected, layout.navigation?.[currentUrl]?.topMenu?.dropdown.options.data[0]?.slug)  // Link go to that 1 data 
									: route(menu.route?.all)  // If data is above than 1 data then Link to 'All list'
					"
					:title="capitalize(menu.tooltip)"
					class="group flex justify-center items-center cursor-pointer py-1  lg:space-x-4 px-14 grow"
					:class="[layout.navigation?.[currentUrl]?.topMenu?.dropdown?.options?.data?.length > 1 ? 'hover:text-indigo-600' : '']"
				>
					<FontAwesomeIcon
						:icon="menu.icon"
						class="h-5 lg:h-3.5 w-auto pr-1 group-hover:opacity-100 opacity-30 transition duration-100 ease-in-out"
						aria-hidden="true" />
					<span class="hidden lg:inline capitalize">{{ menu.label }}</span>
				</Link>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ref } from "vue"
import { router } from "@inertiajs/vue3"
import TopBarMenu from "@/Components/Navigation/TopBarMenu.vue"
import { capitalize } from "@/Composables/capitalize"
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
	faPeopleArrows,
	faPersonDolly,
	faClipboardList,
	faFolder,
	faFolders,
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
	faPeopleArrows,
	faPersonDolly,
	faClipboardList,
	faFolder,
	faFolders,
	faBrowser
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
</script>

<style scoped>
.center-content{
	width: 100%;
    align-items: center;
    justify-content: center;
}
</style>
