<script setup lang="ts">
import { inject, ref } from "vue"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faThumbtack } from "@fal"
import { faThumbtack as fasThumbtack, faDiamond } from "@fas"
import { ctrans } from "@/Composables/useTrans"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"

library.add(faThumbtack, fasThumbtack, faDiamond)

const props = defineProps<{
	option: {
		id: number
		name: string
		price: number
		unit: string
		currency_code: string
		is_pinned?: boolean
		is_pallet_handling?: boolean
	}
}>()

const locale = inject("locale", aikuLocaleStructure)

const isTogglingPin = ref(false)

const togglePin = async () => {
	if (isTogglingPin.value) {
		return
	}

	isTogglingPin.value = true
	const isPinned = !props.option.is_pinned

	try {
		await axios.patch(route("grp.models.fulfilment.services.update", { service: props.option.id }), {
			is_pinned: isPinned,
		})
		props.option.is_pinned = isPinned
	} catch (error) {
		notify({
			title: ctrans("Something went wrong."),
			text: ctrans("Failed to pin the service"),
			type: "error",
		})
	} finally {
		isTogglingPin.value = false
	}
}
</script>

<template>
	<div class="flex w-full items-center gap-x-2" :class="option.is_pinned ? 'font-semibold' : ''">
		<div class="grow">
			<FontAwesomeIcon
				v-if="option.is_pallet_handling"
				v-tooltip="ctrans('Special service')"
				icon="fas fa-diamond"
				class="text-teal-500 text-sm"
				fixed-width
				aria-hidden="true" />
			{{ option.name }}
			<span class="text-sm font-normal text-gray-400">
				({{ locale.currencyFormat(option.currency_code, option.price) }}/{{ option.unit }})
			</span>
		</div>

		<button
			type="button"
			v-tooltip="option.is_pinned ? ctrans('Unpin, stop showing at the top') : ctrans('Pin to the top of the list')"
			class="shrink-0 px-1"
			@mousedown.stop.prevent
			@click.stop.prevent="togglePin">
			<LoadingIcon v-if="isTogglingPin" />
			<FontAwesomeIcon
				v-else
				:icon="option.is_pinned ? 'fas fa-thumbtack' : 'fal fa-thumbtack'"
				:class="option.is_pinned ? 'text-[--app-accent]' : 'text-gray-400 hover:text-gray-600'"
				fixed-width
				aria-hidden="true" />
		</button>
	</div>
</template>
