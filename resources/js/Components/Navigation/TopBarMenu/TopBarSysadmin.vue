<template>
	<div class="flex items-center divide-x divide-gray-200 justify-center rounded overflow-hidden">
		<Link
			v-for="menu in layout.navigation.sysadmin.topMenu"
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
