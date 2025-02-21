<script setup lang="ts">
import { ref, watch } from "vue"
import { Link } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faArrowAltRight, faExternalLink } from "@fal";
import Dialog from 'primevue/dialog';

library.add(faExternalLink, faArrowAltRight)
  
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
	<Dialog
		v-model:visible="model"
		:modal="true"
		:closable="true"
		dismissable-mask
		
		@hide="closeModal">
		<!-- Main Content -->
		<div class="w-72 md:w-full md:max-w-xl flex flex-col items-center justify-center p-2 sm:p-8 space-y-6">
			<!-- Header with icon and title -->
			<div class="flex flex-col items-center space-y-3">
				<svg
					class="h-12 sm:h-16 w-12 sm:w-16 text-green-500"
					fill="none"
					stroke="currentColor"
					viewBox="0 0 24 24">
					<path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M5 13l4 4L19 7" />
				</svg>
				<h2 class="text-2xl sm:text-3xl font-bold text-gray-800 text-center">Customer Approved!</h2>
			</div>
			<!-- Informational message -->
			<p class="text-center text-gray-600 text-sm sm:text-base max-w-md">
				The customer has been approved. You can now view their details.
			</p>
			<!-- Responsive Side-by-Side Buttons -->
			<div class="w-full flex flex-col sm:flex-row justify-center items-center sm:space-x-4 space-y-4 sm:space-y-0">
				 <Link :href="directCustomer(props.approvedCustomer)">
                    <Button label="View Details" iconRight="fal fa-external-link" type="primary" size="l" />
				</Link> 
				<Button @click="closeModal" label="Continue Approving"  type="secondary" size="l" />
			</div>  
		</div>
	</Dialog>
</template>
