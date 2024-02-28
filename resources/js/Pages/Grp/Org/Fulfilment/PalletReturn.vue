<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, useForm, router } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref, watch } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/TableHistories.vue"
import Timeline from "@/Components/Utils/Timeline.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Modal from "@/Components/Utils/Modal.vue"
import TablePalletReturn from "@/Components/PalletReturn/tablePalletReturn.vue"
import TablePalletReturnsDelivery from "@/Components/Tables/TablePalletReturnsDelivery.vue"

const props = defineProps<{
	title: string
	tabs: object
	pallets?: object
	data?: object
	history?: object
	pageHead: object
	updateRoute: object
	uploadRoutes: object
    palletRoute : object
}>()
let currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab)
const loading = ref(false)
const timeline = ref({ ...props.data.data })
const dataModal = ref({ isModalOpen: false })
const formAddPallet = useForm({ notes: "", customer_reference: "" })
const formMultiplePallet = useForm({ number_pallets: 1 })
const openModal = ref(false)

const handleFormSubmitAddPallet = (data: object, closedPopover: Function) => {
	loading.value = true
	formAddPallet.post(route(data.route.name, data.route.parameters), {
		preserveScroll: true,
		onSuccess: () => {
			closedPopover()
			formAddPallet.reset("notes", "customer_reference")
			loading.value = false
		},
		onError: (errors) => {
			loading.value = false
			console.error("Error during form submission:", errors)
		},
	})
}

const handleFormSubmitAddMultiplePallet = (data: object, closedPopover: Function) => {
	loading.value = true
	formMultiplePallet.post(route(data.route.name, data.route.parameters), {
		preserveScroll: true,
		onSuccess: () => {
			closedPopover()
			formMultiplePallet.reset("number_pallets")
			loading.value = false
		},
		onError: (errors) => {
			loading.value = false
			console.error("Error during form submission:", errors)
		},
	})
}

/* const updateState = async ({ step, options }) => {

  const foundState = options.find((item) => item.key === timeline.value.state)
  const set = step.key == timeline.state || step.index < foundState.index
  if (!set) {
    try {
      const response = await axios.patch(
        route(props.updateRoute.route.name, props.updateRoute.route?.parameters),
        { state: get(step, 'key') }
      )
      console.log(response)
      timeline.value = response.data.data
    } catch (error) {
      console.log('error', error)
    }
  }
} */

const handleClick = (action) => {
	const href = action.route?.name
		? route(action.route?.name, action.route?.parameters)
		: action.href?.name
		? route(action.href?.name, action.href?.parameters)
		: "#"
	const method = action.route?.method ?? "get"
	const data = action.route?.method !== "get" ? props.dataToSubmit : null
	router[method](href, data, {
		onBefore: (visit) => {
			loading.value = true
		},
		onSuccess: (page) => {
			timeline.value = page.props.data.data
		},
		onFinish: (visit) => {
			loading.value = false
		},
	})
}



const component = computed(() => {
	const components = {
		pallets: TablePalletReturnsDelivery,
		history: TableHistories,
	}
	return components[currentTab.value]
})

const onUploadOpen = (action) => {
	dataModal.value.isModalOpen = true
	dataModal.value.uploadRoutes = action.route
}

watch(
	props,
	(newValue) => {
		timeline.value = newValue.data.data
	},
	{ deep: true }
)

</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead">
		<template #button-group-add-pallet="{ action: action }">
			<Button
				:style="action.button.style"
				:label="action.button.label"
				:icon="action.button.icon"
				:iconRight="action.button.iconRight"
				:key="`ActionButton${action.button.label}${action.button.style}`"
				:tooltip="action.button.tooltip"
				@click="() => (openModal = true)"
				class="capitalize inline-flex items-center h-full rounded-none text-sm border-none font-medium shadow-sm focus:ring-transparent focus:ring-offset-transparent focus:ring-0" />
		</template>
	</PageHeading>
	<div class="border-b border-gray-200">
		<Timeline :options="timeline.timeline" :state="timeline.state" :slidesPerView="6" />
	</div>
	<Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
	<component
		:is="component"
		:data="props[currentTab]"
		:state="timeline.state"
		:tab="currentTab"></component>

	<Modal :isOpen="openModal" @onClose="openModal = false">
		<div class="h-96 overflow-y-auto">
			<TablePalletReturn :palletRoute="palletRoute" @onClose="()=>openModal = false"/>
		</div>
	</Modal>
</template>
