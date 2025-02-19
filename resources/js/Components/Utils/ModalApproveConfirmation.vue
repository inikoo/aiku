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
	<Modal :isOpen="model" @onClose="closeModal" :closeButton="true" width="w-[500px]">
		<div class="flex flex-col items-center p-8 space-y-6">
			<!-- Header with icon and title -->
			<div class="flex flex-col items-center space-y-2">
				<svg
					class="h-12 w-12 text-green-500"
					fill="none"
					stroke="currentColor"
					viewBox="0 0 24 24">
					<path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M5 13l4 4L19 7" />
				</svg>
				<h2 class="text-2xl font-bold text-gray-800">Customer Approved!</h2>
			</div>
			<!-- Informational message -->
			<p class="text-center text-gray-600">
				The customer has been approved successfully. You can now proceed to view their
				details.
			</p>
			<!-- Action button -->
			<div class="w-full flex justify-center">
				<Link
					:href="directCustomer(props.approvedCustomer)"
					class="w-full md:w-auto px-2 py-3  text-black font-semibold rounded-full text-centertransition">
					Go to Customer
				</Link>
			</div>
            <div class="w-full flex justify-end">
                <button @click="closeModal"
                        class="w-full md:w-auto px-6 py-3 bg-gray-300 text-gray-800 font-semibold rounded-full text-center hover:bg-gray-400 transition">
                Continue Approve
                </button>
            </div>
		</div>
	</Modal>
</template>
