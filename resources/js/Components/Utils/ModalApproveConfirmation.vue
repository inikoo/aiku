<script setup lang="ts">
import { ref, watch } from "vue"
import Modal from "@/Components/Utils/Modal.vue"
import { Link } from "@inertiajs/vue3"

const model = defineModel<boolean>()

const props = defineProps<{
	approvedCustomer?: any
}>()

const emit = defineEmits<{
	(e: "close"): void
}>()

const closeModal = () => {
	model.value = false
	emit("close")
}

watch(model, (newVal) => {
	if (newVal) {
		// Reset or initialize additional states if needed
	}
})

function directCustomer(customer: any) {
	if (!customer) return "#"
	switch (route().current()) {
		case "grp.org.fulfilments.show.crm.customers.pending_approval.index":
			return route("grp.org.fulfilments.show.crm.customers.show", [
				route().params["organisation"],
				route().params["fulfilment"],
				customer.slug,
			])
		default:
			return "#"
	}
}
</script>

<template>
	<Modal :isOpen="model" @onClose="closeModal" :closeButton="true" width="w-[400px]">
		<!-- Main Content -->
		<div class="flex flex-col items-center p-4 space-y-4">
			<!-- Header with icon and title -->
			<div class="flex flex-col items-center space-y-1">
				<svg
					class="h-10 w-10 text-green-500"
					fill="none"
					stroke="currentColor"
					viewBox="0 0 24 24">
					<path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M5 13l4 4L19 7" />
				</svg>
				<h2 class="text-xl font-bold text-gray-800">Customer Approved!</h2>
			</div>
			<!-- Informational message -->
			<p class="text-center text-gray-600 text-sm">
				The customer has been approved. You can now view their details.
			</p>
			<!-- Centered button for viewing customer details -->
			<div class="w-full flex justify-center">
				<Link
					:href="directCustomer(props.approvedCustomer)"
					class="w-full md:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-full text-center transition shadow-sm">
					View Details

				</Link>
			</div>
		</div>
		<!-- Footer with continue button -->
		<div class="px-4 py-3 border-t border-gray-200 flex justify-end">
			<Button
				@click="closeModal"
				class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-full text-center transition shadow-xs">
				Continue Approve
			</Button>
		</div>
	</Modal>
</template>
