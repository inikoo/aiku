<template>
    <div class="flex gap-x-6">
		<Link
			v-for="topMenu in layout.navigation.hr.topMenu"
			:href="route(topMenu.route.name)"
			class="space-x-1 flex items-center"
		>
			<FontAwesomeIcon
				:icon="topMenu.icon"
				class="h-4 w-4 border border-indigo-600 p-1 text-indigo-700 rounded"
				aria-hidden="true"
			/>
			<span class="text-sm text-gray-700 capitalize">{{ topMenu.label }}</span>
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

</script>

<style lang="scss" scoped>

</style>