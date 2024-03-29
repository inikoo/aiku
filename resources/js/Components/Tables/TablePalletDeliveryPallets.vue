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
import { Link, router, useForm } from "@inertiajs/vue3"
import Icon from "@/Components/Icon.vue"
import { faTimesSquare } from "@fas"
import { faTrashAlt, faPaperPlane, faInventory } from "@far"
import { faSignOutAlt, faTruckLoading, faTimes } from "@fal"
import { useLayoutStore } from "@/Stores/retinaLayout"
import FieldEditableTable from "@/Components/FieldEditableTable.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ref, watch, defineEmits } from "vue"
import ButtonEditTable from "@/Components/ButtonEditTable.vue"
import LocationFieldDelivery from "@/Components/LocationFieldDelivery.vue"
import StoredItemProperty from '@/Components/StoredItemsProperty.vue'
import { routeType } from "@/types/route"
import { Table as TSTable } from "@/types/Table"

library.add( faTrashAlt, faSignOutAlt, faPaperPlane, faInventory, faTruckLoading, faTimesSquare, faTimes )

const props = defineProps<{
	data: TSTable
	tab?: string
	state?: string
	tableKey: number
	locationRoute: routeType
	storedItemsRoute: {
		index : routeType
		store : routeType
	}
}>()

const emits = defineEmits<{
    (e: 'renderTableKey'): void
}>()

const onSaved = async (pallet: { form : {}}, fieldName: string) => {
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

		// Setelah 5 detik, back to  normal
		setTimeout(() => {
			pallet.form.wasSuccessful = false
		}, 3000)
	}
}

</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5" :key="tableKey">
		<template #cell(state)="{ item: palletDelivery }">
			<Icon :data="palletDelivery['state_icon']" class="px-1" />
		</template>

        <!-- Column: Customer Reference -->
		<template #cell(customer_reference)="{ item: item }">
			<div v-if="state == 'in-process'" class="w-full">
				<FieldEditableTable :data="item" @onSave="onSaved" fieldName="customer_reference" placeholder="Enter customer reference" />
			</div>
			<div v-else>{{ item["customer_reference"] }}</div>
		</template>

        <!-- Column: Notes -->
		<template #cell(notes)="{ item: item }">
			<div v-if="state == 'in-process'" class="">
				<FieldEditableTable :data="item" @onSave="onSaved" fieldName="notes" placeholder="Enter pallet notes"/>
			</div>
			<div v-else>{{ item["notes"] }}</div>
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
            <!-- State: in process -->
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

            <!-- State: not received -->
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

            <!-- State: Received -->
			<div v-else-if="props.state == 'received' || props.state == 'not-received'" class="flex">
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

                <!-- <ButtonEditTable
                    :type="pallet.state == 'booked-in' ? 'primary' : 'tertiary'"
                    :icon="['fal', 'inventory']"
                    :tooltip="'Booked In'"
                    :key="pallet.index"
                    :size="'xs'"
                    routeName="bookInRoute"
                    :data="pallet"
                    @onSuccess="() => emits('renderTableKey')"
                    /> -->
                <LocationFieldDelivery
                    :pallet="pallet"
                    @renderTableKey="() => emits('renderTableKey')"
                    :locationRoute="locationRoute" />
			</div>
		</template>
	</Table>
</template>
