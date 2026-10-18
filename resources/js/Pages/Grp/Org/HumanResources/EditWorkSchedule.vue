<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faClock } from "@fal"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import ShiftScheduleForm from "@/Components/HumanResources/ShiftScheduleForm.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"

library.add(faClock)

defineProps<{
	pageHead: {}
	title: string
	schedule: {
		id: number
		name: string
		days?: any[]
	}
}>()

const backToIndex = () => {
	router.visit(route("grp.org.hr.shift_schedules.index", route().params.organisation))
}

const onSaved = () => {
	notify({
		title: ctrans("Success"),
		text: ctrans("Shift schedule saved."),
		type: "success",
	})
	backToIndex()
}
</script>

<template>
	<div>
		<Head :title="capitalize(title)" />
		<PageHeading :data="pageHead" />

		<div class="px-4 py-6 sm:px-6 lg:px-8">
			<div class="mx-auto max-w-3xl rounded-lg border border-gray-200 bg-white p-5 sm:p-6">
				<ShiftScheduleForm :schedule="schedule" @saved="onSaved" @cancel="backToIndex" />
			</div>
		</div>
	</div>
</template>
