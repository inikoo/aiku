<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from "@/Components/Table/Table.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"

import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { Link } from "@inertiajs/vue3"
import Icon from "@/Components/Icon.vue"
import { faTimesSquare } from "@fas"
import { faTrashAlt, faPaperPlane, faInventory } from "@far"
import { faTruckLoading, faStickyNote, faPallet, faBox, faSortSizeUp } from "@fal"
import FieldEditableTable from "@/Components/FieldEditableTable.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import ButtonEditTable from "@/Components/ButtonEditTable.vue"
import LocationFieldDelivery from "@/Components/LocationFieldDelivery.vue"
import StoredItemProperty from '@/Components/StoredItemsProperty.vue'
import { routeType } from "@/types/route"
import { Table as TSTable } from "@/types/Table"

import '@/Composables/Icon/PalletStateEnum'
import { trans } from "laravel-vue-i18n"
import { ref } from "vue"
import Tag from "@/Components/Tag.vue"

library.add(faTrashAlt, faPaperPlane, faInventory, faTruckLoading, faStickyNote, faTimesSquare, faPallet, faBox, faSortSizeUp)

const props = defineProps<{
    data: TSTable
    tab?: string
    state?: string
    tableKey: number
    locationRoute: routeType
    rentalRoute: routeType
    rentalList?: []
	storedItemsRoute: {
		store: routeType
		index: routeType
		delete: routeType
	}
}>()

const isLoading = ref<string | boolean>(false)

const emits = defineEmits<{
	(e: 'renderTableKey'): void
}>()


const typePallet = [
	{ label: trans('Pallet'), value: 'pallet' },
	{ label: trans('Box'), value: 'box' },
	{ label: trans('Oversize'), value: 'oversize' }
]


const onSaved = async (pallet: { form: {} }, fieldName: string) => {
	if (pallet[fieldName] != pallet.form.data()[fieldName]) {
		pallet.form.processing = true
		try {
			await axios.patch(route(pallet.updateRoute.name, pallet.deleteRoute.parameters), {
				[fieldName]: pallet.form.data()[fieldName],
			})
			onSavedSuccess(pallet, fieldName)

		} catch (error: any) {
			onSavedError(error, pallet, fieldName)
		}

		setTimeout(() => {
			pallet.form.wasSuccessful = false
		}, 3000)
	}
}

const onSavedRental = async (pallet: { form: {} }, fieldName: string) => {
    isLoading.value = fieldName + pallet.id
    // console.log('ee', pallet, '===', fieldName, '==', pallet.form.data())
	if (pallet[fieldName] != pallet.form.data()[fieldName]) {
		pallet.form.processing = true
		try {
			await axios.patch(route(pallet.updatePalletRentalRoute.name, pallet.updatePalletRentalRoute.parameters), {
				[fieldName]: pallet.form.data()[fieldName],
			})
			onSavedSuccess(pallet, fieldName)

		} catch (error: any) {
			onSavedError(error, pallet)
		}

		setTimeout(() => {
			pallet.form.wasSuccessful = false
		}, 3000)
	}
    isLoading.value = false
}



const onSavedSuccess = (pallet: { form: {} }, fieldName: string) => {
	pallet.form.processing = false
	pallet.form.wasSuccessful = true
	pallet.form.hasErrors = false
	pallet.form.clearErrors()
	pallet[fieldName] = pallet.form.data()[fieldName]
}

const onSavedError = (error: {}, pallet: { form: {} }) => {
	pallet.form.processing = false
	pallet.form.wasSuccessful = false
	pallet.form.hasErrors = true
	if (error.response && error.response.data && error.response.data.errors) {
		const errors = error.response.data.errors
		const setErrors = {}
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


</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5" :key="tableKey">
		<!-- Column: type pallet icon -->
		<template #cell(type_icon)="{ item: pallet }">
			<Icon :data="pallet.type_icon" class="px-1" />
			<Icon :data="pallet['state_icon']" class="px-1" />
		</template>


		<!-- Column: state-->
		<template #cell(state)="{ item: palletDelivery }">
			<Icon :data="palletDelivery['state_icon']" class="px-1" />
		</template>


		<!-- Column: Type pallet -->
		<template #cell(type)="{ item: pallet }">
            <div class="w-32">
            <!-- <pre>{{ pallet.id }}</pre> -->
                <FieldEditableTable
                    :key="'typePallet' + pallet.id"
                    :data="pallet"
                    @onSave="onSaved"
                    :options="typePallet"
                    :fieldType="'select'"
                    label="label"
                    valueProp="value"
                    fieldName="type"
                    placeholder="Enter customer type"
                />
            </div>
		</template>


		<!-- Column: Pallet Reference -->
		<template #cell(customer_reference)="{ item }">
			<div v-if="state == 'in-process'" class="w-full">
				<FieldEditableTable
                    :data="item"
                    @onSave="onSaved"
                    fieldName="customer_reference"
					placeholder="Enter customer reference"
                />
			</div>

			<div v-else class="space-x-1 space-y-2">
				<span v-if="item.customer_reference">{{ item.customer_reference }}</span>
				<span v-if="item.notes" class="text-gray-400 text-xs">
					<FontAwesomeIcon icon='fal fa-sticky-note' class='text-gray-400' fixed-width aria-hidden='true' />
					{{ item.notes }}
				</span>
                <span v-else class="text-gray-400 text-xs">-</span>
			</div>
		</template>


		<!-- Column: Notes -->
		<template #cell(notes)="{ item }">
			<div v-if="state == 'in-process'" class="min-w-32">
				<FieldEditableTable
                    :data="item"
                    @onSave="onSaved"
                    fieldName="notes"
                    placeholder="Enter pallet notes"
                />
			</div>
			<div v-else>{{ item["notes"] }}</div>
		</template>


		<!-- Column: Stored Items -->
		<template #cell(stored_items)="{ item }">
			<StoredItemProperty
                :pallet="item"
				:saveRoute="item.storeStoredItemRoute"
				:storedItemsRoute="storedItemsRoute"
                :editable="props.state == 'in-process'"
                @renderTable="() => emits('renderTableKey')"
            />
		</template>


		<!-- Column: Set Location -->
		<template #cell(location)="{ item: pallet }">
			<div v-if="pallet.state == 'received' || pallet.state == 'booked-in' || pallet.state == 'booking-in'" class="flex gap-x-1 gap-y-2 items-center">
				<LocationFieldDelivery
                    :key="pallet.state"
                    :pallet="pallet"
					@renderTableKey="() => emits('renderTableKey')"
                    :locationRoute="locationRoute"
                />
			</div>
            
            <template v-else>
                <Tag v-if="pallet.location_code" :label="pallet.location_code" />
                <div v-else class="pl-4 text-gray-400">-</div>
            </template>
		</template>


		<!-- Column: Rental -->
		<template #cell(rental)="{ item: pallet }">
			<div v-if="props.state == 'booked-in'">{{ pallet.rental_name }}</div>
			<div v-else class="w-64">
                <FieldEditableTable
                    :key="'rental_id' + pallet.id"
                    :options="props.rentalList"
                    :data="pallet"
                    :isLoading="isLoading === 'rental_id' + pallet.id"
                    @onSave="onSavedRental"
                    fieldType="select"
                    fieldName="rental_id"
                    placeholder="Enter rental"
                    label="name"
                    value-prop="id"
                />
            </div>
		</template>


		<!-- Column: Actions -->
		<template #cell(actions)="{ item: pallet }">
			<!-- State: Delete Pallet (in_process) -->
			<div v-if="props.state == 'in-process'">
				<Link
                    :href="route(pallet.deleteRoute.name, pallet.deleteRoute.parameters)"
                    method="delete"
                    preserve-scroll
                    as="div"
                    @start="() => isLoading = 'delete' + pallet.id"
                    v-tooltip="'Delete this pallet'"
                >
                    <Button icon="far fa-trash-alt" :loading="isLoading === 'delete' + pallet.id" type="negative" />
				</Link>
			</div>


			<!-- State: not received -->
			<div v-else-if="pallet.state == 'not-received'">
				<ButtonEditTable class="mx-2" type="secondary" label="Undo" :capitalize="false" :size="'xs'"
					:key="pallet.index" routeName="undoNotReceivedRoute" :data="pallet"
					@onSuccess="() => emits('renderTableKey')" />
			</div>


			<!-- State: Received -->
			<div v-else-if="(props.state == 'received' || props.state == 'booking-in' || props.state == 'not-received') && !pallet.location_id" class="flex">
				<ButtonEditTable class="mx-2" :type="pallet.state == 'not-received' ? 'secondary' : 'negative'"
					:icon="['fal', 'times']" tooltip="Set as not received" :size="'xs'" :key="pallet.index"
					routeName="notReceivedRoute" :data="pallet" @onSuccess="() => emits('renderTableKey')" />
			</div>
		</template>
	</Table>
</template>
