<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import TableShiftSchedules from "@/Components/Tables/Grp/Org/HumanResources/TableShiftSchedules.vue"
import ShiftScheduleForm from "@/Components/HumanResources/ShiftScheduleForm.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { ref } from "vue"
import { notify } from "@kyvg/vue3-notification"
import Dialog from "primevue/dialog"
import Button from "@/Components/Elements/Buttons/Button.vue"

defineProps<{
	pageHead: {}
	title: string
	data: {}
}>()

const isCreateDialogOpen = ref(false)

const onCreated = () => {
	isCreateDialogOpen.value = false
	notify({
		title: ctrans("Success"),
		text: ctrans("Done, shift added successfully"),
		type: "success",
	})
	router.reload()
}
</script>

<template>
	<div>
		<Head :title="capitalize(title)" />
		<PageHeading :data="pageHead">
			<template #button-btn-create>
				<Button
					@click="isCreateDialogOpen = true"
					type="create"
					icon="fal fa-plus"
					:label="ctrans('Create Shift')"
					capitalize />
			</template>
		</PageHeading>

		<TableShiftSchedules :data="data" />

		<Dialog
			v-model:visible="isCreateDialogOpen"
			modal
			:draggable="false"
			:header="ctrans('Create shift schedule')"
			:style="{ width: '44rem', maxWidth: 'calc(100vw - 2rem)' }">
			<ShiftScheduleForm v-if="isCreateDialogOpen" @saved="onCreated" @cancel="isCreateDialogOpen = false">
				<template #hint>
					<p class="text-xs text-gray-500 mt-2">
						{{ ctrans("You can change these hours at any time from the shift's Edit button.") }}
					</p>
				</template>
			</ShiftScheduleForm>
		</Dialog>
	</div>
</template>
