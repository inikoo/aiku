<template>
	<div class="flex items-center divide-x divide-gray-200 justify-center rounded overflow-hidden">
		<Link
			v-for="menu in layout.navigation.hr.topMenu"
			:href="route(menu.route.name)"
			class="group flex justify-center items-center cursor-pointer space-x-1 px-3">
			<FontAwesomeIcon
				:icon="menu.icon"
				class="h-4 w-4 p-1 text-gray-800 group-hover:bg-indigo-600 group-hover:text-white rounded opacity-60 group-hover:opacity-100"
				aria-hidden="true" />
			<span class="text-gray-800 capitalize">{{ menu.label }}</span>
		</Link>
	</div>
</template>

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { useLayoutStore } from "@/Stores/layout"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faNetworkWired, faTerminal, faCalendar, faStopwatch, faChessClock } from "../../../../private/pro-light-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"

library.add(faNetworkWired, faTerminal, faCalendar, faStopwatch, faChessClock)
const layout = useLayoutStore()
console.log(layout.navigation)
</script>

<style lang="scss" scoped>

</style>