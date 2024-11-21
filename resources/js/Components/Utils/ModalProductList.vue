<script setup lang="ts">
import Modal from "@/Components/Utils/Modal.vue"
import { onMounted, onUnmounted, ref, watch } from "vue"
import DataTable from "primevue/datatable"
import Column from "primevue/column"
import IconField from "primevue/iconfield"
import InputIcon from "primevue/inputicon"
import InputNumber from "primevue/inputnumber"
import InputText from "primevue/inputtext"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faMinus, faPlus, faSearch } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { routeType } from "@/types/route"
import axios from "axios"
import { debounce } from "lodash"
import { Action } from "@/types/Action"
import { useForm } from "@inertiajs/vue3"

library.add(faSearch, faPlus, faMinus)

const props = defineProps<{
  fetchRoute: routeType
  action: any
}>()

const emits = defineEmits<{
  (e: "optionsList", value: any[]): void
}>()

const model = defineModel()
const products = ref<any[]>([]) // Product list
const optionsMeta = ref(null)
const optionsLinks = ref(null)
const isLoading = ref<string | boolean>(false)
const searchQuery = ref("") // Search input state

const closeModal = () => {
  model.value = false
}

// Cleanup and reset products
const resetProducts = () => {
  products.value = []
  optionsMeta.value = null
  optionsLinks.value = null
}

// Utility to construct fetch URLs
const getUrlFetch = (additionalParams: {}) => {
  return route(props.fetchRoute.name, {
    ...props.fetchRoute.parameters,
    ...additionalParams,
  })
}

// Fetch product list function
const fetchProductList = async (url?: string) => {
  isLoading.value = "fetchProduct"
  const urlToFetch = url || route(props.fetchRoute.name, props.fetchRoute.parameters)

  try {
    const response = await axios.get(urlToFetch)
    const data = response.data

    if (url && optionsLinks.value?.next) {
      products.value = [...products.value, ...data.data]
    } else {
      resetProducts()
      products.value = data.data
    }

    optionsMeta.value = data.meta
    optionsLinks.value = data.links

    emits("optionsList", products.value)
  } catch (error) {
    console.error("Error fetching product list:", error)
  } finally {
    isLoading.value = false
  }
}

// Debounced fetch logic
const debouncedFetch = debounce((query: string) => {
  fetchProductList(getUrlFetch({ "filter[global]": query }))
}, 500)

const onSearchQuery = (query: string) => {
  debouncedFetch(query)
}

const formProducts = useForm({
  quantity_ordered: 0,
})

const onSubmitAddProducts = (data: any, slotProps: any) => {
  const purchaseOrderId = slotProps.data?.purchase_order_id

  if (purchaseOrderId && slotProps.data.quantity_ordered > 1) {
    formProducts
      .transform(() => ({
        quantity_ordered: slotProps.data.quantity_ordered,
      }))
      .patch(
        route(
          slotProps?.data?.updateRoute?.name || "#",
          { ...slotProps.data.updateRoute?.parameters }
        )
      )
  } else {
    formProducts.post(
      route(data.route?.name || "#", {
        ...data.route?.parameters,
        historicSupplierProduct: slotProps.data.historic_id,
        orgStock: slotProps.data.org_stock_id,
      })
    )
  }
}

// Scroll listener for pagination
const onFetchNext = async () => {
  if (optionsLinks.value?.next && !isLoading.value) {
    await fetchProductList(optionsLinks.value.next)
  }
}

onMounted(() => {
  const tableBody = document.querySelector(".p-datatable-scrollable-body")
  if (tableBody) {
    tableBody.addEventListener("scroll", onFetchNext)
  }

  fetchProductList() // Initial fetch
})

onUnmounted(() => {
  const tableBody = document.querySelector(".p-datatable-scrollable-body")
  if (tableBody) {
    tableBody.removeEventListener("scroll", onFetchNext)
  }
})
</script>

<template>
	<KeepAlive>
		<Modal :isOpen="model" @onClose="closeModal" :closeButton="true" width="w-auto">
			<div class="flex flex-col justify-between h-[600px] overflow-y-auto pb-4 px-3">
				<div>
					<!-- Title -->
					<div class="flex justify-center py-2 text-gray-600 font-medium mb-3">
						<div>
							<div class="flex gap-x-0.5">Product List</div>
						</div>
					</div>

					<!-- Search and Table -->
					<div class="flex items-start gap-x-2 gap-y-2 flex-col mt-4">
						<div class="flex flex-wrap gap-x-2 gap-y-2">
							<div class="card">
								<DataTable
									:value="products"
									scrollable
									scrollHeight="400px"
									tableStyle="min-width: 50rem">
									<template #header>
										<div
											class="flex flex-wrap items-center justify-between gap-2">
											<span class="text-xl font-bold">Products</span>
											<IconField>
												<InputIcon>
													<FontAwesomeIcon
														icon="fal fa-search"
														class="text-gray-500"
														fixed-width
														aria-hidden="true" />
												</InputIcon>
												<InputText
													v-model="searchQuery"
													placeholder="Search"
													@input="onSearchQuery(searchQuery)" />
											</IconField>
										</div>
									</template>

									<Column field="code" header="Code"></Column>
									<Column header="Image">
										<template #body="slotProps">
											<img
												:src="`https://primefaces.org/cdn/primevue/images/product/${slotProps.data.image}`"
												:alt="slotProps.data.image"
												class="w-24 rounded" />
										</template>
									</Column>
									<Column field="name" header="Description"></Column>

									<Column header="Action">
										<template #body="slotProps">
											<InputNumber
												v-model="slotProps.data.quantity_ordered"
												showButtons
												buttonLayout="horizontal"
												inputClass="w-16"
												:min="0"
												:max="99">
												<template #incrementbuttonicon>
													<FontAwesomeIcon
														icon="fal fa-plus"
														class="text-gray-500"
														fixed-width
														aria-hidden="true"
														@click="
															() =>
																onSubmitAddProducts(
																	action,
																	slotProps
																)
														" />
												</template>
												<template #decrementbuttonicon>
													<FontAwesomeIcon
														icon="fal fa-minus"
														class="text-gray-500"
														fixed-width
														aria-hidden="true" />
												</template>
											</InputNumber>
										</template>
									</Column>

									<!-- Footer -->
									<template #footer>
										<div class="text-center">
											In total there are {{ products ? products.length : 0 }}
											products.
										</div>
									</template>
								</DataTable>
							</div>
						</div>
					</div>
				</div>
			</div>
		</Modal>
	</KeepAlive>
</template>
