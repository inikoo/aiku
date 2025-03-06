<script setup lang="ts">
import { inject, ref, computed } from "vue"
import axios from "axios"
import { router } from "@inertiajs/vue3" // Import Inertia router
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faEdit, faTrash, faSave } from "@fortawesome/free-solid-svg-icons"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { faPlus } from "@far"
import { notify } from "@kyvg/vue3-notification"
import { trans } from "laravel-vue-i18n"

// Add icons to the library
library.add(faEdit, faTrash, faPlus, faSave)

// Global key for the search query parameter
const SEARCH_PARAM_KEY = "global"

// Define component props with defaults
const props = withDefaults(
	defineProps<{
		showRedBorder: boolean
		widget: any[] // Expecting an array of objects with id, contact_name, email, etc.
		visual?: any
	}>(),
	{
		widget: () => [],
	}
)

// Create a reactive copy of the widget items for deletion handling
const widgetItems = ref([...props.widget])

// Inject global locale and layout stores if needed
const locale = inject("locale", aikuLocaleStructure)
const layoutStore = inject("layout", layoutStructure)

// Track whether we are in edit mode
const isEditing = ref(false)

// Flag to indicate unsaved changes
const hasChanges = ref(false)

// Reactive array for newly added user inputs (with search capability)
// Each object: { query: string, suggestions: string[] }
const newUserInputs = ref<{ query: string; suggestions: string[] }[]>([])

// Reactive array for newly added external email inputs (strings)
const newExternalEmailInputs = ref<string[]>([])

// Computed property to check if there are any subscriptions (existing or new)
const hasSubscriptions = computed(() => {
	return (
		widgetItems.value.length > 0 ||
		newUserInputs.value.length > 0 ||
		newExternalEmailInputs.value.length > 0
	)
})

// Toggle edit mode
const toggleEdit = () => {
	isEditing.value = true
}

// Handler for "Add User" – adds a new user input with search capability
const addUser = () => {
	console.log("Add User clicked")
	newUserInputs.value.push({ query: "", suggestions: [] })
	hasChanges.value = true
}

// Handler for "Add External Email" – adds a new external email input
const addExternalEmail = () => {
	console.log("Add External Email clicked")
	newExternalEmailInputs.value.push("")
	hasChanges.value = true
}

// Delete an existing subscriber using the delete endpoint
const deleteWidgetItem = async (index: number) => {
	const item = widgetItems.value[index]
	try {
		await axios.delete("grp.models.fulfilment.outboxes.subscriber.delete", {
			params: { id: item.id },
		})
		widgetItems.value.splice(index, 1)
		hasChanges.value = true
	} catch (error) {
		console.error("Error deleting subscriber", error)
	}
}

// Delete a newly added user input field
const deleteUserInput = (index: number) => {
	newUserInputs.value.splice(index, 1)
	hasChanges.value = true
}

// Delete a newly added external email input field
const deleteExternalEmailInput = (index: number) => {
	newExternalEmailInputs.value.splice(index, 1)
	hasChanges.value = true
}

// Save changes using the Inertia router
const saveChanges = () => {
	console.log("Save clicked")
	const payload: any = {}
	// If external email inputs exist, send only external_email.
	// Otherwise, if user inputs exist, send only user_id.
	if (newExternalEmailInputs.value.length > 0) {
		payload.external_email = newExternalEmailInputs.value
	} else if (newUserInputs.value.length > 0) {
		payload.user_id = newUserInputs.value.map((input) => input.query)
	}

	// Define routeToSubmit for the store endpoint
	const routeToSubmit = {
		name: "grp.models.fulfilment.outboxes.subscriber.store",
		parameters: [
			route().params['fulfilment'],
			route().params['outbox'],
		], // Add any parameters if needed
	}

	router.post(route(routeToSubmit.name, routeToSubmit.parameters), payload, {
		preserveScroll: true,
		onSuccess: () => {
			notify({
				title: trans("Succes"),
				text: trans("Successfully attach") + ` ${scope}.`,
				type: "success",
			})
			newUserInputs.value = []
			newExternalEmailInputs.value = []
			hasChanges.value = false
		},
		onError: (errors: any) => {
			console.log(errors,'as');
			
			notify({
				title: trans("Something went wrong."),
				text: trans("Failed to attach") ,
				type: "error",
			})
		},
		onFinish: () => {
			
		},
	})
}

// Handle input change for user field to perform search query via API
const handleUserInput = async (index: number) => {
	hasChanges.value = true
	const query = newUserInputs.value[index].query
	if (!query) {
		newUserInputs.value[index].suggestions = []
		return
	}
	try {
		// Use the global key for the query parameter
		const response = await axios.get("grp.json.fulfilment.outbox.users.index", {
			params: { [SEARCH_PARAM_KEY]: query },
		})
		// Assuming the API returns an array of user suggestions
		newUserInputs.value[index].suggestions = response.data
	} catch (error) {
		console.error("Error fetching user suggestions", error)
		newUserInputs.value[index].suggestions = []
	}
}

// When a suggestion is clicked, set it as the input value and clear suggestions
const selectUserSuggestion = (index: number, suggestion: string) => {
	newUserInputs.value[index].query = suggestion
	newUserInputs.value[index].suggestions = []
	hasChanges.value = true
}
</script>

<template>
	<dl class="mb-2 grid grid-cols-1 md:grid-cols-2 gap-3">
		<div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
			<!-- Card Header with Title -->
			<div class="px-4 py-1">
				<h2 class="text-lg font-semibold">Subscribe</h2>
			</div>
			<!-- Card Body -->
			<div class="p-4">
				<!-- Existing widget items -->
				<div
					v-for="(item, index) in widgetItems"
					:key="index"
					class="flex items-center justify-between border-b border-gray-100 py-1">
					<div class="flex items-center space-x-2">
						<!-- Show edit icon when not in edit mode -->
						<div v-if="!isEditing">
							<FontAwesomeIcon
								:icon="faEdit"
								class="text-blue-500 cursor-pointer"
								@click="toggleEdit" />
						</div>
						<!-- Display contact_name and email in italic -->
						<span class="text-gray-600">
							{{ item.contact_name }} <i>({{ item.email }})</i>
						</span>
					</div>
					<!-- Delete icon appears in edit mode -->
					<div v-if="isEditing">
						<FontAwesomeIcon
							:icon="faTrash"
							class="text-red-500 cursor-pointer"
							@click="deleteWidgetItem(index)" />
					</div>
				</div>

				<!-- Newly added User Inputs with search suggestions -->
				<div v-if="newUserInputs.length" class="mt-2">
					<div
						v-for="(input, index) in newUserInputs"
						:key="'user-' + index"
						class="border-b border-gray-100 py-2">
						<div class="flex items-center justify-between">
							<input
								type="text"
								v-model="input.query"
								@input="handleUserInput(index)"
								placeholder="Enter User"
								class="w-full border border-gray-300 rounded p-2" />
							<FontAwesomeIcon
								:icon="faTrash"
								class="text-red-500 cursor-pointer ml-2"
								@click="deleteUserInput(index)" />
						</div>
						<!-- Suggestions dropdown -->
						<ul
							v-if="input.suggestions.length"
							class="mt-1 border border-gray-300 rounded bg-white">
							<li
								v-for="(suggestion, sIndex) in input.suggestions"
								:key="sIndex"
								class="px-2 py-1 hover:bg-gray-100 cursor-pointer"
								@click="selectUserSuggestion(index, suggestion)">
								{{ suggestion }}
							</li>
						</ul>
					</div>
				</div>

				<!-- Newly added External Email Inputs -->
				<div v-if="newExternalEmailInputs.length" class="mt-2">
					<div
						v-for="(input, index) in newExternalEmailInputs"
						:key="'external-' + index"
						class="flex items-center justify-between border-b border-gray-100 py-2">
						<input
							type="text"
							v-model="newExternalEmailInputs[index]"
							@input="hasChanges.value = true"
							placeholder="Enter External Email"
							class="w-full border border-gray-300 rounded p-2" />
						<FontAwesomeIcon
							:icon="faTrash"
							class="text-red-500 cursor-pointer ml-2"
							@click="deleteExternalEmailInput(index)" />
					</div>
				</div>

				<!-- If there are no subscriptions, display a placeholder text -->
				<div v-if="!hasSubscriptions" class="mt-2">
					<p class="text-gray-600 italic">not subscribe set</p>
				</div>

				<!-- Action Buttons to add new inputs and Save button -->
				<div v-if="isEditing" class="mt-2 flex items-center space-x-4">
					<Button
						label="Add User"
						:type="'secondary'"
						size="s"
						@click="addUser"
						iconRight="far fa-plus" />
					<Button
						label="Add External Email"
						:type="'secondary'"
						size="s"
						@click="addExternalEmail"
						iconRight="far fa-plus" />
					<!-- Save button with icon; enabled only when there are changes -->
					<Button
						label="Save"
						:type="'primary'"
						size="s"
						@click="saveChanges"
						:disabled="!hasChanges"
						iconRight="faSave" />
				</div>
			</div>
		</div>
	</dl>
</template>
