<script setup lang="ts">
import { get, defaultTo, fromPairs, before } from "lodash"
import axios from "axios"
import { onMounted, ref, defineProps, defineExpose } from "vue"
import { useForm,router } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { notify } from "@kyvg/vue3-notification"
import PureInput from "@/Components/Pure/PureInput.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faSpinnerThird, faSearch } from "@fad"
import { library } from "@fortawesome/fontawesome-svg-core"
import { routeType } from "@/types/route"

// Modal: on Add Pallet

library.add(faSpinnerThird, faSearch)

const props = defineProps<{
	dataRoute: routeType
	saveRoute: routeType
	descriptor: {}
	beforeSubmit?: Function
	onFilterDatalist?: Function
}>()

const emits = defineEmits<{
    (e: 'onClose'): void
}>()

const dataList = ref([])
const loading = ref(false)
const form = useForm({ [props.descriptor.key]: [] })
const checkedAll = ref(false)
const tableFilter = useForm({
	search: '',
})


const closeModal = () => {
	emits('onClose')
}

// Method: Fetch data Pallet
const getData = async () => {
	loading.value = true
	try {
		const response = await axios.get(
			route(props.dataRoute.name, props.dataRoute.parameters),
			{ params: { [`${props.descriptor.key}_filter[global]`]: tableFilter.search } } // Changed from { search: tableFilter.filter }
		)
		let finaldata = response.data.data
		if(props.onFilterDatalist) finaldata = props.onFilterDatalist(finaldata)
		dataList.value = finaldata
		loading.value = false
	} catch (error) {
		loading.value = false
		notify({
			title: 'Failed to fetch data',
			text: error.message,
			type: "error",
		})
	}
}

const selectAll = () => {
	const value = []
	if (!checkedAll.value) {
		dataList.value.forEach((item) => value.push(item.id))
		checkedAll.value = true
	} else checkedAll.value = false
	form[props.descriptor.key] = value
}

const onChecked = (value) => {
	if (form.data()[props.descriptor.key].length > dataList.value.length && form.data()[props.descriptor.key].length != 0)
		checkedAll.value = false
	if (form.data()[props.descriptor.key].length == dataList.value.length && form.data()[props.descriptor.key].length != 0)
		checkedAll.value = true
	else checkedAll.value = false
}

// Method: Submit Add Pallet
const onSubmitPallet = async () => {
	let eventData = form[props.descriptor.key]
    if(props.beforeSubmit) eventData = props.beforeSubmit(form[props.descriptor.key],dataList.value)
	router.post(route(props.saveRoute.name, props.saveRoute.parameters), { [props.descriptor.key] : eventData }, {
		preserveScroll: true,
		onBefore: () => {
			loading.value = true
		},
		onFinish: () => {
			loading.value = false
		},
		onSuccess: () => {
			form.reset(`${props.descriptor.key}`)
			checkedAll.value = false
			getData()
			closeModal()
		},
	})
}

onMounted(getData)

defineExpose({
	dataList,
    loading,
	tableFilter,
})

</script>

<template>
	<div class="sm:flex sm:items-center px-2">
		<div class="flex-auto">
			<div class="w-1/4 pt-2">
				<PureInput v-model="tableFilter.search" placeholder="Search" :loading="loading" :copy-button="true"
					@keyup.enter.native="getData">
					<template #copyButton>
						<div class="flex justify-center items-center px-2 absolute inset-y-0 right-0 gap-x-1 cursor-pointer opacity-20 hover:opacity-75 active:opacity-100"
							@click="() => getData()">
							<FontAwesomeIcon icon="fad fa-search" class="text-lg leading-none" aria-hidden="true" />
						</div>
					</template>
				</PureInput>
			</div>
		</div>

        <!-- Button: Add Pallet -->
		<div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
			<Button :style="'create'" :label="`add ${descriptor.title}`" :disabled="!form[props.descriptor.key].length" :key="form[props.descriptor.key].length"
				@click="onSubmitPallet" />
		</div>
	</div>
    
	<div class="px-1 sm:px-1 lg:px-8">
		<div class="mt-8 flow-root">
			<div class="-mx-4 -my-2 sm:-mx-6 lg:-mx-8">
				<div
					class="inline-block min-w-full py-2 align-middle shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
					<table class="min-w-full border-separate border-spacing-0">
						<thead class="bg-gray-50">
							<tr>
								<th scope="col"
									class="sticky top-0 z-10 border-b border-gray-300 bg-white py-3.5 pl-4 pr-3 text-left text-sm font-semibold backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">
									<input type="checkbox" :checked="checkedAll" @change="selectAll"
										class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600" />
								</th>
								<th v-for="(item, index) in descriptor.column" :key="`header-${item.key}`" scope="col"
									class="sticky top-0 z-10 hidden border-b border-gray-300 bg-white px-3 py-3.5 text-left text-sm font-semibold backdrop-blur backdrop-filter sm:table-cell">
									<slot :name="`head-${item.key}`" :data="{ headData : item , index : index }">
										{{ item.label }}
									</slot>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(pallet, index) in dataList" :key="pallet.id">
								<td
									:class="[index !== dataList.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium sm:pl-6 lg:pl-8']">
									<input type="checkbox" :id="pallet.id" :value="pallet.id" v-model="form[props.descriptor.key]"
										@change="onChecked"
										class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600" />
								</td>
								<td v-for="(column, columnIndex) in descriptor.column" :key="`column-${pallet.id}`"
									:class="[index !== dataList.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap hidden px-3 py-4 text-sm text-gray-500 sm:table-cell']">
									<slot :name="`column-${column.key}`" :data="{ columnData : pallet, index : columnIndex }">
										{{ defaultTo(get(pallet, [column.key]), "-") }}
									</slot>
									
								</td>
							</tr>
						</tbody>
					</table>

					<div v-if="loading" class="flex justify-center items-center w-full h-full p-12">
						<div>
							<FontAwesomeIcon icon="fad fa-spinner-third" class="animate-spin w-6" aria-hidden="true" />
						</div>
					</div>
					
                    <div v-if="dataList.length == 0 && !loading"
						class="flex justify-center items-center w-full h-full p-12">
						<div>
							No Data
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
