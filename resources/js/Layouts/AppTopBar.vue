<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ref, computed } from "vue"
import { router } from "@inertiajs/vue3"
import TopBarMenu from "@/Components/Navigation/TopBarMenu.vue"
import { capitalize } from "@/Composables/capitalize"
import { useLayoutStore } from "@/Stores/layout.js"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import {
	faStore,
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
	faUserPlus,
	faCube,
	faTruck,
	faFileInvoiceDollar,
} from "@/../private/pro-light-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"

library.add(
	faStore,
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
	faUserPlus,
	faCube,
	faTruck,
	faFileInvoiceDollar,
)
const layout = useLayoutStore()

const props = defineProps<{
	tenantName: string
}>()

const currentUrl = ref()
const currentRoute = ref()
router.on("navigate", (event) => {
	currentRoute.value = route().current()
	let splitUrl = event.detail.page.url.split("/")[1]
	currentUrl.value = splitUrl.split("?")[0]
})


const generateLink = (menu) => {

    // If the slug is not null Then the menu go to that slug
    if(layout?.navigation?.[currentUrl.value]?.currentData.slug){
        return route(menu.route?.selected, layout?.navigation?.[currentUrl.value]?.currentData.slug);
    }
    console.log('A')


    //if active route is same as 'All List' slug
    if(currentRoute.value != layout?.navigation?.[currentUrl.value]?.route ){


        if( route().v().params?.[Object.keys(route().params)[0]]){
            return route(menu.route?.selected, route().v().params)
        }


        let numberDataItemsInMenu=layout.navigation?.[currentUrl.value]?.topMenu?.dropdown?.options?.data?.length;

        if(numberDataItemsInMenu <=1){
            return route(menu.route?.all)
        }else{
            return route(menu.route?.all)
        }
    }

    //console.log('B')

    const numberOptionsInMenu=layout.navigation?.[currentUrl.value]?.topMenu?.dropdown?.options?.data?.length;


   // console.log(numberOptionsInMenu)

    if(numberOptionsInMenu==0){
        return route(menu.route?.selected, layout.navigation?.[currentUrl.value]?.topMenu?.dropdown.options.data[0]?.slug)
    }

    //console.log('C')

    return route(menu.route?.all)


}

const helperMarketplaces = [
	{
		title: 'Agents Marketplace',
		label: 'Agents',
		route: 'procurement.marketplace.agents.index',
	},
	{
		title: 'Supplier Marketplace',
		label: 'Supplier',
		route: 'procurement.marketplace.suppliers.index',
	},
]

const compCurrentSlug = computed(() => {
	return layout.navigation?.[currentUrl.value]?.currentData?.slug
})

const urlPath = (routeName: string, params) => {
	// To return the pathname (/some/path/name/) from the url
	return new URL(route(routeName, params)).pathname
}

const checkRight = (abcdef) => {
	return route().v().params ? Object.keys(route().v().params).length > 0
			? urlPath(currentRoute.value, route().params).includes(urlPath(abcdef['route']['selected'], route().params))
			: urlPath(currentRoute.value, route().params).includes(urlPath(abcdef['route']['all']))
		: urlPath(currentRoute.value, route().params).includes(urlPath(abcdef['route']['all']))

}

</script>

<template>
	<div class="flex flex-1 items-center justify-between lg:justify-start">

		<!-- Tenant Logo and Name -->
		<Link :href="route('dashboard.show')"
			class="md:pl-3 flex items-center h-full xl:overflow-hidden space-x-2 mr-6 xl:w-56 xl:pr-2 xl:border-r-2 xl:border-gray-200 xl:mr-0">
		<img class="h-7 md:h-5 shadow" :src="`/media/group/${layout.tenant.logo_id}`" :alt="layout.tenant.code" />
		<span class="hidden leading-none md:inline font-logo text-indigo-700 xl:truncate">
			{{ layout.tenant.name }}
		</span>
		</Link>

		<div class="flex">
			<!-- For Procurement -->
			<div v-if="currentUrl === 'procurement'" class="ml-4 flex items-center overflow-hidden">
				<div class="px-2 h-full flex justify-center items-center rounded-l border border-indigo-300">
					<FontAwesomeIcon icon="fal fa-store"
						class="w-auto text-indigo-500"
						aria-hidden="true" />
				</div>

				<div
					class="text-sm inline-flex place-self-center rounded-r justify-center border-solid border border-l-0 border-indigo-300">
					<Link v-for="helperMarketplace in helperMarketplaces" :href="route(helperMarketplace.route)"
						:title="helperMarketplace.title"
						class="group text-gray-400 flex justify-center items-center cursor-pointer h-7 py-1 space-x-1 px-3" :class="[
							[currentRoute == helperMarketplace.route ? 'text-indigo-600' : 'hover:text-gray-600']
						]">
					<p class="hidden lg:inline capitalize">{{ helperMarketplace.label }}</p>
					</Link>
				</div>
			</div>


			<!-- Left Menu -->
			<div v-if="currentUrl && layout.navigation?.[currentUrl]?.topMenu && layout.navigation?.[currentUrl]?.topMenu?.subSections" class="text-sm flex items-center divide-x divide-gray-300 justify-center overflow-hidden">
				<Link
					v-for="menu in layout.navigation?.[currentUrl]?.topMenu.subSections" :href="route(menu.route.name)"
					class="group flex justify-end items-center cursor-pointer py-1 space-x-1 px-4 md:px-4 lg:px-4"
					:class="[(route(currentRoute, route().params)).includes(urlPath(menu.route.name)) ? 'text-indigo-600' : 'text-gray-600']"
					:title="capitalize(menu.label)">
					<FontAwesomeIcon :icon="menu.icon"
						class="h-5 lg:h-3.5 w-auto group-hover:opacity-100 opacity-70 transition duration-100 ease-in-out"
						aria-hidden="true" />
					<span class="hidden lg:inline capitalize whitespace-nowrap">{{ menu.label }}</span>
					<!-- {{ (route(route().current(), route().params)) }} -- {{ urlPath(menu.route.name) }} -->
					<!-- {{ (route(route().current(), route().params)).includes(urlPath(menu.route.name)) }} -->
				</Link>
			</div>

			<!-- Dropdown -->
			<TopBarMenu
				v-if=
                    "currentUrl &&
                    layout.navigation?.[currentUrl]?.topMenu &&
				    layout.navigation?.[currentUrl]?.topMenu.dropdown &&
				    layout.navigation?.[currentUrl]?.topMenu?.dropdown.options.data.length > 1"
				:currentPage="currentUrl" />

			<!-- Right Menu -->
			<div class="text-sm text-gray-600 inline-flex place-self-center rounded-r justify-center border-solid "
				:class="[
					layout.navigation?.[currentUrl]?.topMenu?.dropdown?.options?.data?.length > 1
						? 'border border-l-0 border-indigo-300'
						: currentUrl && layout.navigation?.[currentUrl]?.topMenu && layout.navigation?.[currentUrl]?.topMenu?.subSections
							? 'border-l border-gray-300 divide-x divide-gray-300 ' : ' divide-x divide-gray-300']">
				<!-- href:
					If the slug is initial state (which is null) then the menu will show all shop,
					but if current route is contain params (slug of options) then the links is linkselected with that params (handle for refresh page that the state is back to null),
					if the 'show all shop' only contain 1 data then the links is directly to that 1 data
				-->

				<Link v-if="currentUrl &&
					layout.navigation?.[currentUrl]?.topMenu &&
					layout.navigation?.[currentUrl]?.topMenu.dropdown?.subsections"
					v-for="(menu, index) in layout.navigation?.[currentUrl]?.topMenu.dropdown.subsections"
					:href="generateLink(menu)"
					:title="capitalize(menu.tooltip)"
					class="group flex justify-center items-center cursor-pointer h-7 py-1 space-x-1 px-4"
					:class="[
						layout.navigation?.[currentUrl]?.topMenu?.dropdown?.options?.data?.length > 1 ? 'hover:text-indigo-600' : '',
						menu.route.all == 'inventory.warehouses.index' && !layout?.navigation?.[currentUrl]?.currentData.slug ? 'border-l-4 border-l-transparent' : '',
						checkRight(menu) && index != 0 ? 'text-indigo-600' : '',
						(route().v().params
								? urlPath(currentRoute, route().v().params) == urlPath(menu['route']['selected'], route().v().params)
								: urlPath(currentRoute, route().v().params) == (urlPath(menu['route']['all'])))
							? 'text-indigo-600' : ''
					]"
				>
					<FontAwesomeIcon
						v-if="menu.route.all == 'inventory.warehouses.index' && !layout?.navigation?.[currentUrl]?.currentData.slug && layout.navigation?.[currentUrl]?.topMenu?.dropdown?.options?.data?.length != 1"
						icon="fal fa-bars"
						class="w-auto pr-1 group-hover:opacity-100 opacity-70 transition duration-100 ease-in-out"
						aria-hidden="true" />
					<FontAwesomeIcon v-else :icon="menu.icon"
						class="w-auto pr-1 group-hover:opacity-100 opacity-70 transition duration-100 ease-in-out"
						aria-hidden="true" />

					<!-- If Menu != Warehouses then don't show the label (only icon) -->
					<p v-if="menu.route.selected != 'inventory.warehouses.show'" class="hidden lg:inline capitalize whitespace-nowrap">
						<!-- If menu linked to Shop Dashboard then label = 'Shop' instead default (Shops) -->
						{{ layout.navigation?.[currentUrl]?.currentData?.slug ? menu.route.selected == 'shops.show' ? 'Shop' : menu.label : menu.label }}
					</p>
				</Link>
			</div>
		</div>
	</div>
</template>

<style scoped></style>
