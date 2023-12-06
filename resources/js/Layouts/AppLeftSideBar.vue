<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 13:49:56 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { Link } from "@inertiajs/vue3"
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
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
 } from '@fal'
import { useLayoutStore } from "@/Stores/layout.js"
import { computed } from "vue";
import Image from "@/Components/Image.vue";

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
     faLightbulb,
 )

const layout = useLayoutStore()
//const props = defineProps(["currentRoute"])

/*
const currentModule = computed(() => {
    const route=props['currentRoute'];
    return route.substring(0, route.indexOf("."))
});

 */

//const currentModule=layout.currentModule;

const isHover = ref(false)

const currentIndexModule = computed(() => {
	return Object.keys(layout.navigation).indexOf(layout.currentModule)
})



onMounted(() => {
	window.addEventListener('keydown', handleKey)
})
onUnmounted(() => {
	window.removeEventListener('keydown', handleKey)
})

// console.log(Object.keys(layout.navigation).length)

const handleKey = (event: any) => {
	// If Arrow Up key is pressed and the element is hovered and not the first index
	if (event.key === 'ArrowUp' && isHover.value && currentIndexModule.value != 0) {
		const prevTab = ref(layout.navigation[Object.keys(layout.navigation)[currentIndexModule.value-1]])
		prevTab.value.route.all
			? router.get(route(prevTab.value.route.all, prevTab.value.routeParameters))
			: router.get(route(prevTab.value.route, prevTab.value.routeParameters))
	}
	// If Arrow Down key is pressed and the element is hovered and not the last index
	else if (event.key === 'ArrowDown' && isHover.value && currentIndexModule.value != Object.keys(layout.navigation).length -1) {
		const nextTab = ref(layout.navigation[Object.keys(layout.navigation)[currentIndexModule.value+1]])
		nextTab.value.route.all
			? router.get(route(nextTab.value.route.all, nextTab.value.routeParameters))
			: router.get(route(nextTab.value.route, nextTab.value.routeParameters))
	}
}


const generateRoute = (item) => {
    const scope=item.scope
    if (scope && typeof item.route === "object" && item.route !== null) {
        if (scope == "shops") {
            if (layout.currentShopData.slug) {
                return route(item.route.selected, layout.currentShopData.slug);
            }
            return route(item.route.all);
        }
        if (scope == "websites") {
            if (layout.currentWebsiteData.slug) {
                return route(item.route.selected, layout.currentWebsiteData.slug);
            }
            return route(item.route.all);
        }
        if (scope == "warehouses") {
            if (layout.currentWarehouseData.slug) {
                return route(item.route.selected, layout.currentWarehouseData.slug);
            }
            return route(item.route.all);
        }
    }
    return route(item.route, item.routeParameters);
}

const generateLabel = (item) => {
    const scope = item.scope
    if (typeof item.label === "object" && item.label !== null) {
        if (
            (scope == "shops" && layout.currentShopData.slug) ||
            (scope == "websites" && layout.currentWebsiteData.slug) ||
            (scope == "warehouses" && layout.currentWarehouseData.slug)
        ){
            return item.label.selected;
        }
        return item.label.all;
    }
    return item.label;
};

</script>

<template>
	<div class="w-8/12 mt-11 fixed md:border-r md:border-gray-200 bg-white md:flex md:flex-col md:inset-y-0 md:w-10 lg:mt-10 xl:w-56"
		@mouseenter="isHover = true" @mouseleave="isHover = false">
		<div class="flex flex-grow flex-col h-full overflow-y-auto custom-hide-scrollbar border-r border-gray-200 pb-4">
			<Link :href="route('grp.dashboard.show')" class="flex flex-col justify-center text-indigo-700 font-logo md:hidden py-3 text-center gap-y-2">
        <Image :src="layout.group.logo" class="h-7 md:h-5 shadow" :alt="layout.group.code"/>
				<span>{{ layout.group.name }}</span>
			</Link>
			<div class="flex flex-grow flex-col pb-16">
				<nav class="flex-1 space-y-1" aria-label="Sidebar">
         {{ layout.groupNavigation}}
					<!-- LeftSide Links -->
					<Link
						v-for="(item, itemKey) in layout.groupNavigation"
						:key="itemKey"
						:href="generateRoute(item)"
						:class="[
							itemKey === layout.currentModule
								? 'border-indigo-600 bg-indigo-50 text-indigo-600'
								: 'border-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-900',
							'group flex items-center border-l-4 text-sm font-medium px-0 xl:px-3 py-2',
						]"
						:aria-current="itemKey === layout.currentModule ? 'page' : undefined"
					>
						<div>
							<img v-if="item.name == 'dashboard'" src="@/../art/logo/png/logo-aiku.png" alt="Aiku Logo" class="h-4 aspect-square"
								:class="[ itemKey === layout.currentModule
											? 'text-indigo-500'
											: 'text-gray-400 group-hover:text-gray-600',
										'ml-2 mr-3 flex-shrink-0 h-4 w-4'
								]"
							>
							<FontAwesomeIcon
								v-else
								aria-hidden="true"
								:class="[
									itemKey === layout.currentModule
										? 'text-indigo-500'
										: 'text-gray-400 group-hover:text-gray-600',
									'ml-2 mr-3 flex-shrink-0 h-4 w-4',
								]"
								:icon="item.icon" />
						</div>
						<span class="md:hidden xl:block capitalize">{{ generateLabel(item) }}</span>
					</Link>

                  <Link
                    v-for="(item, itemKey) in layout.organisation.navigation"
                    :key="itemKey"
                    :href="generateRoute(item)"
                    :class="[
							itemKey === layout.currentModule
								? 'border-indigo-600 bg-indigo-50 text-indigo-600'
								: 'border-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-900',
							'group flex items-center border-l-4 text-sm font-medium px-0 xl:px-3 py-2',
						]"
                    :aria-current="itemKey === layout.currentModule ? 'page' : undefined"
                  >
                    <div>
                      <img v-if="item.name == 'dashboard'" src="@/../art/logo/png/logo-aiku.png" alt="Aiku Logo" class="h-4 aspect-square"
                           :class="[ itemKey === layout.currentModule
											? 'text-indigo-500'
											: 'text-gray-400 group-hover:text-gray-600',
										'ml-2 mr-3 flex-shrink-0 h-4 w-4'
								]"
                      >
                      <FontAwesomeIcon
                        v-else
                        aria-hidden="true"
                        :class="[
									itemKey === layout.currentModule
										? 'text-indigo-500'
										: 'text-gray-400 group-hover:text-gray-600',
									'ml-2 mr-3 flex-shrink-0 h-4 w-4',
								]"
                        :icon="item.icon" />
                    </div>
                    <span class="md:hidden xl:block capitalize">{{ generateLabel(item) }}</span>
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
