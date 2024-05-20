<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from "@/Components/Table/Table.vue"
import { library } from "@fortawesome/fontawesome-svg-core"

import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { Link } from "@inertiajs/vue3"
import Icon from "@/Components/Icon.vue"
import { faTimesSquare } from "@fas"
import { faTrashAlt, faPaperPlane, faInventory } from "@far"
import { faSignOutAlt, faTruckLoading, faTimes } from "@fal"
import FieldEditableTable from "@/Components/FieldEditableTable.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import ButtonEditTable from "@/Components/ButtonEditTable.vue"
import LocationFieldDelivery from "@/Components/LocationFieldDelivery.vue"
import { routeType } from "@/types/route"
import { Table as TSTable } from '@/types/Table'
import StoredItemProperty from '@/Components/StoredItemsProperty.vue'

library.add( faTrashAlt, faSignOutAlt, faPaperPlane, faInventory, faTruckLoading, faTimesSquare, faTimes )
const props = defineProps<{
	data: TSTable
	tab?: string
	state: string
	tableKey: number
    storedItemsRoute: {
		index : routeType
		store : routeType
	}
}>()

console.log('RetinaPalletDelivery', props)
const emits = defineEmits<{
    (e: 'renderTableKey'): void
}>()

// Method: Field save
const onSaveField = async (pallet: any, fieldName: string) => {
	if (pallet[fieldName] != pallet.form.data()[fieldName]) {
		pallet.form.processing = true
		try {
			await axios.patch(route(pallet.updateRoute.name, pallet.deleteRoute.parameters), {
				[fieldName]: pallet.form.data()[fieldName],
			})
			pallet.form.processing = false
			pallet.form.wasSuccessful = true
			pallet.form.hasErrors = false
			pallet.form.clearErrors()
			pallet[fieldName] = pallet.form.data()[fieldName]
		} catch (error: any) {
			pallet.form.processing = false
			pallet.form.wasSuccessful = false
			pallet.form.hasErrors = true
			if (error.response && error.response.data && error.response.data.errors) {
				const errors = error.response.data.errors
				const setErrors: any = {}
				for (const er in errors) {
					setErrors[er] = errors[er][0]
				}
				pallet.form.setError(setErrors)
			} else {
				if (error.response.data.message)
					notify({
						title: "Failed to update",
						text: error.response.data.message,
						type: "error",
					})
			}
		}

		// Setelah 5 detik, back to  normal
		setTimeout(() => {
			pallet.form.wasSuccessful = false
		}, 3000)
	}
}

const typePallet = [
    { label : 'Pallet', value : 'pallet'},
    { label : 'Box', value : 'box'},
    { label : 'Oversize', value : 'oversize'}
]

</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5" :key="tableKey">
		<template #cell(state)="{ item: palletDelivery }">
			<Icon :data="palletDelivery['state_icon']" class="px-1" />
		</template>

        <!-- Column: Customer Reference -->
		<template #cell(customer_reference)="{ item }">
            <FieldEditableTable v-if="state == 'in-process'" :data="item" @onSave="onSaveField" fieldName="customer_reference" placeholder="Enter customer reference" />
			<div v-else>{{ item.customer_reference }}</div>
		</template>

        <!-- Column: Notes -->
		<template #cell(notes)="{ item }">
			<div v-if="state == 'in-process'" class="">
				<FieldEditableTable :data="item" @onSave="onSaveField" fieldName="notes" placeholder="Enter pallet notes"/>
			</div>
			<div v-else>{{ item.notes }}</div>
		</template>

        <!-- Column: Stored Items -->
		<template #cell(stored_items)="{ item: item }">
            <StoredItemProperty
                :pallet="item"
                @renderTable="() => emits('renderTableKey')"
                :storedItemsRoute="storedItemsRoute"
                :state="props.state"
            />
		</template>

        <!-- Column: Actions -->
		<template #cell(actions)="{ item: pallet }">
			<div v-if="props.state == 'in-process'">
				<Link
					:href="route(pallet.deleteRoute.name, pallet.deleteRoute.parameters)"
					method="delete"
					as="div"
					:onSuccess="() => emits('renderTableKey')"
                    v-tooltip="'Delete this pallet'"
                    class="w-fit"
                >
                    <Button icon="far fa-trash-alt" type="negative" />
				</Link>
			</div>
<!--
			<div v-else-if="pallet.state == 'not-received'">
				<ButtonEditTable
					class="mx-2"
					type="secondary"
                    label="Set as received"
					tooltip="Set as received"
                    :capitalize="false"
					:size="'xs'"
					:key="pallet.index"
					routeName="undoNotReceivedRoute"
					:data="pallet"
					@onSuccess="() => emits('renderTableKey')" />
			</div>

			<div v-else>
				<div class="flex">
					<ButtonEditTable
						class="mx-2"
						:type="pallet.state == 'not-received' ? 'secondary' : 'negative'"
						:icon="['fal', 'times']"
						tooltip="Set as not received"
						:size="'xs'"
						:key="pallet.index"
						routeName="notReceivedRoute"
						:data="pallet"
						@onSuccess="() => emits('renderTableKey')" />

					<LocationFieldDelivery
						:pallet="pallet"
						@renderTableKey="() => emits('renderTableKey')"
					/>
				</div>
			</div>-->
		</template>

		<template #cell(type)="{ item: pallet }">
            <div>
				<FieldEditableTable :data="pallet"  @onSave="onSaveField" :options="typePallet" :fieldType="'select'" fieldName="type" placeholder="Enter customer type" />
			</div>
		</template>

		<template #cell(type_icon)="{ item: pallet }">
			<Icon :data="pallet.type_icon" class="px-1" />
		</template>
	</Table>
</template>
