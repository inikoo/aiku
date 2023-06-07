<template>
	<div class="flex justify-between items-center gap-5">
		<div class="block pb-3 pl-3 xl:w-56">
			<img class="h-4 mt-4 xl:h-6" src="/art/logo-color-trimmed.png" alt="Aiku" />
			<span class="font-logo mb-1 mr-2 xl:hidden whitespace-nowrap text-sm">
				{{ props.tenantName }}
			</span>
		</div>
		<!-- <component :is="component[currentUrl]" /> -->
		<div
			class="flex items-center divide-x divide-gray-100 justify-center  overflow-hidden">
			<Link
                v-if="currentUrl && layout.navigation[currentUrl].topMenu && layout.navigation[currentUrl].topMenu.subSections"
				v-for="menu in layout.navigation[currentUrl].topMenu.subSections"
				:href="route(menu.route.name)"
				class="group flex justify-center items-center cursor-pointer space-x-1 px-4">
				<FontAwesomeIcon
					:icon="menu.icon"
					class="h-3.5 w-3.5 pr-1 text-gray-600 group-hover:opacity-100  opacity-40"
					aria-hidden="true" />
				<span class="text-gray-500 capitalize">{{ menu.label }}</span>
			</Link>
		</div>

        <!-- Dropdown -->
        <div v-if="currentUrl && layout.navigation[currentUrl].topMenu && layout.navigation[currentUrl].topMenu.dropdown">
            <TopBarDropdown :currentPage="currentUrl"/>
        </div>

        <!-- Right Menu -->
        <div class="flex items-center divide-x divide-gray-200 justify-center rounded overflow-hidden">
			<Link
                v-if="currentUrl && layout.navigation[currentUrl].topMenu && layout.navigation[currentUrl].topMenu.dropdown?.subsections"
				v-for="menu in layout.navigation[currentUrl].topMenu.dropdown.subsections"
				:href="route(menu.route.all)"
                :title="menu.tooltip"
				class="group flex justify-center items-center cursor-pointer space-x-1 px-4"
            >
				<FontAwesomeIcon
					:icon="menu.icon"
					class="h-4 w-4 p-1 text-gray-800 group-hover:bg-indigo-600 group-hover:text-white rounded opacity-60 group-hover:opacity-100"
					aria-hidden="true" />
				<span class="text-gray-800 capitalize">{{ menu.label }}</span>
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
import { faNetworkWired, faTerminal, faCalendar, faStopwatch, faChessClock, faUserAlien, faCog, faBox, faBoxesAlt } from "@/../private/pro-light-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"

library.add(faNetworkWired, faTerminal, faCalendar, faStopwatch, faChessClock, faUserAlien, faCog, faBox, faBoxesAlt)
const layout = useLayoutStore()

const props = defineProps<{
	tenantName: string
}>()

const currentUrl = ref()
router.on("navigate", (event) => {
	currentUrl.value = event.detail.page.url.split("/")[1]
})

</script>

<style scoped></style>
