<script setup lang="ts">
import { get, defaultTo, fromPairs } from "lodash"
import axios from "axios"
import { onMounted, ref, defineProps } from "vue"
import { useForm } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { notify } from "@kyvg/vue3-notification"
import PureInput from "@/Components/Pure/PureInput.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faSpinnerThird, faSearch } from "@fad"
import { library } from "@fortawesome/fontawesome-svg-core"

// Modal: on Add Pallet

library.add(faSpinnerThird, faSearch)

const props = defineProps<{
	palletRoute: object
	descriptor : object
}>()

const emits = defineEmits()
const dataList = ref([])
const loading = ref(false)
const form = useForm({ pallets: [] })
const checkedAll = ref(false)
const tableFilter = useForm({
	search: null,
})


const closeModal = () => {
    emits('onClose')
}

const getData = async () => {
	loading.value = true
	try {
		const response = await axios.get(
			route(props.palletRoute.index.name, props.palletRoute.index.parameters),
			{ params: { ['pallets_filter[global]']: tableFilter.search } } // Changed from { search: tableFilter.filter }
		)
		dataList.value = response.data.data
		loading.value = false
	} catch (error) {
		console.log("error", error)
		loading.value = false
		notify({
			title: error.response.statusText,
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
	form.pallets = value
}

const onChecked = (value) => {
	if (form.data().pallets.length > dataList.value.length && form.data().pallets.length != 0)
		checkedAll.value = false
	if (form.data().pallets.length == dataList.value.length && form.data().pallets.length != 0)
		checkedAll.value = true
	else checkedAll.value = false
}

const onSubmitPallet = () => {
	form.post(route(props.palletRoute.store.name, props.palletRoute.store.parameters), {
		preserveScroll: true,
		onBefore: () => {
			loading.value = true
		},
        onFinish: () => {
			loading.value = false
        },
		onSuccess: () => {
			form.reset("pallets")
      		checkedAll.value = false
			getData()
			closeModal()
		},
	})
}

onMounted(getData)
console.log('inii',props)
</script>

<template>   
	<div class="px-4 sm:px-6 lg:px-8">
    <!-- {{ form.pallets }} -->
		<div class="sm:flex sm:items-center">
			<div class="sm:flex-auto">
				<div class="w-1/4 pt-2">
					<PureInput
						v-model="tableFilter.search"
						placeholder="Search"
						:loading="loading"
						:copy-button="true"
                        @keyup.enter.native="getData"
           >
		   <template #copyButton>
			<div class="flex justify-center items-center px-2 absolute inset-y-0 right-0 gap-x-1 cursor-pointer opacity-20 hover:opacity-75 active:opacity-100" @click="()=>getData()">
                <FontAwesomeIcon icon="fad fa-search"
                    class="text-lg leading-none"
                    aria-hidden="true" />
            </div>
		   </template>
		</PureInput>
				</div>
			</div>
			<div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
				<Button :style="'create'" label="Add Pallet" :disabled="!form.pallets.length" :key="form.pallets.length" @click="onSubmitPallet"></Button>
			</div>
		</div>
		<div class="mt-8 flow-root">
			<div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
				<div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
					<div class=" shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg h-[290px] table-wrp block ">
						<table class=" divide-y divide-gray-300  w-full">
							<thead class="bg-gray-50 sticky top-0">
								<tr>
									<th
										scope="col"
										class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
										<input
											type="checkbox"
											:checked="checkedAll"
											@change="selectAll"
											class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600" />
									</th>
									<th
										scope="col"
										class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
										Reference
									</th>
									<th
										scope="col"
										class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
										Customer Reference
									</th>
									<th
										scope="col"
										class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
										Note
									</th>
								</tr>
							</thead>
							<tbody class="divide-y divide-gray-200 bg-white  overflow-y-auto">
								<tr v-for="pallet in dataList" :key="pallet.id">
									<td
										class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
										<input
											type="checkbox"
											:id="pallet.id"
											:value="pallet.id"
											v-model="form.pallets"
											@change="onChecked"
											class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600" />
									</td>
									<td
										class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
										{{ defaultTo(get(pallet, ["reference"]), "-") }}
									</td>
									<td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
										{{ defaultTo(get(pallet, ["customer_reference"]), "-") }}
									</td>
									<td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
										{{ defaultTo(get(pallet, ["note"]), "-") }}
									</td>
								</tr>
							</tbody>
						</table>
						<div v-if="loading" class="flex justify-center items-center w-full h-full p-12">
							<div>
								<FontAwesomeIcon
									icon="fad fa-spinner-third"
									class="animate-spin w-6"
									aria-hidden="true" />
							</div>
						</div>
						<div v-if="dataList.length == 0 && !loading" class="flex justify-center items-center w-full h-full p-12">
							<div>
								No Data
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
