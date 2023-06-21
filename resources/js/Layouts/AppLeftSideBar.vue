<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 13:49:56 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { Link } from "@inertiajs/vue3";
import { library } from "@fortawesome/fontawesome-svg-core"
import {
	faHome,
	faDollyFlatbedAlt,
	faConveyorBeltAlt,
	faUsers,
	faUserHardHat,
	faBars,
	faUsersCog,
	faTachometerAltFast,
	faInventory,
	faStoreAlt,
	faUser,
	faIndustry,
    faBoxUsd,
	faDollyEmpty,
	faShoppingCart,
	faAbacus,
	faParachuteBox,
	faChevronDown,
    faTasksAlt,
    faBullhorn,
    faLightbulb
} from "@/../private/pro-light-svg-icons"
import { useLayoutStore } from "@/Stores/layout"
import { computed } from "vue";

library.add(
	faHome,
	faDollyFlatbedAlt,
	faConveyorBeltAlt,
	faUsers,
	faUserHardHat,
	faBars,
	faUsersCog,
	faTachometerAltFast,
	faInventory,
	faStoreAlt,
	faUser,
	faIndustry,
    faBoxUsd,
	faDollyEmpty,
	faShoppingCart,
	faAbacus,
	faParachuteBox,
	faChevronDown,
    faTasksAlt,
    faBullhorn,
    faLightbulb
)

const layout = useLayoutStore()
const props = defineProps(["currentRoute"])

const currentModule = computed(() => {
    const route=props['currentRoute'];
    return route.substring(0, route.indexOf("."))
});

</script>

<template>
	<div class="w-8/12 mt-11 fixed md:border-r md:border-gray-200 md:bg-gray-100 md:flex md:flex-col md:inset-y-0 md:w-10 lg:mt-10 xl:w-56">
		<div class="flex flex-grow bg-white flex-col h-full overflow-y-auto custom-hide-scrollbar border-r border-gray-200 pb-4">
			<div class="font-logo md:hidden xl:block py-3 text-center">
				{{ layout.tenant.name }}
			</div>

			<div class="flex flex-grow flex-col pb-16">
				<nav class="flex-1 space-y-1" aria-label="Sidebar">
					<!-- LeftSide Links -->
					<Link
						v-for="(item, itemKey) in layout.navigation"
						:key="itemKey"
						:href="route(item.route, item['routeParameters'])"
						:class="[
							itemKey === currentModule
								? 'border-indigo-600 bg-indigo-50 text-indigo-600'
								: 'border-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-900',
							'group flex items-center border-l-4 text-sm font-medium px-0 xl:px-3 py-2',
						]"
						:aria-current="itemKey === currentModule ? 'page' : undefined"
					>
						<FontAwesomeIcon
							aria-hidden="true"
							:class="[
								itemKey === currentModule
									? 'text-indigo-500'
									: 'text-gray-400 group-hover:text-gray-600',
								'ml-2 mr-3 flex-shrink-0 h-4 w-4',
							]"
							:icon="item.icon" />
						<span class="md:hidden xl:block capitalize">{{ item.name }}</span>
					</Link>
				</nav>
			</div>
		</div>
	</div>
</template>

<style>
/* Hide scrollbar for Chrome, Safari and Opera */
.custom-hide-scrollbar::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.custom-hide-scrollbar {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}</style>