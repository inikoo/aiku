<template>
	<div class="flex gap-x-6">
		<Link
			v-for="topMenu in layout.navigation.sysadmin.topMenu"
			:href="route(topMenu.route.name)"
			class="space-x-1 flex items-center"
		>
			<FontAwesomeIcon
				:icon="topMenu.icon"
				class="h-4 w-4 border border-indigo-600 p-1 text-indigo-700 rounded"
				aria-hidden="true"
			/>
			<span class="text-gray-700 capitalize">{{ topMenu.label }}</span>
		</Link>
	</div>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3"
import { computed } from "vue"
import { useLayoutStore } from "@/Stores/layout"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faTerminal, faUserAlien, faCog } from "../../../../private/pro-light-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"
library.add(faTerminal, faUserAlien, faCog)

// import { usePage } from '@inertiajs/inertia-vue3'
// const { page } = usePage()
// console.log(window.location.href)
// console.log(route('sysadmin.users.index'))

// const isActive = computed(() => (link) => window.location.href === link)

// router.on('navigate', (event) => {
//     console.log("===============")
//     console.log(event.detail.page)
// })
const layout = useLayoutStore()
// console.log(layout.navigation.sysadmin.topMenu)
</script>

<style lang="scss" scoped></style>
