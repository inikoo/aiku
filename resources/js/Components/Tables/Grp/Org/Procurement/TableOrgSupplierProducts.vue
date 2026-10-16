<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 11 Aug 2024 10:11:50 Central Indonesia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { OrgSupplierProduct } from "@/types/org-supplier-product"
import { ctrans, ctransChoice } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import { computed, ref } from "vue"
import Dialog from "primevue/dialog"
import Select from "primevue/select"
import { notify } from "@kyvg/vue3-notification"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheck, faCopy, faPlus, faTimes } from "@fal"
import PingIcon from "@/Components/Utils/PingIcon.vue"
import CompletenessBar from "@/Components/Procurement/CompletenessBar.vue"
import MoveToFactoryDialog, { MoveProducts } from "@/Components/Procurement/MoveToFactoryDialog.vue"

library.add(faCheck, faCopy, faPlus, faTimes)

const props = defineProps<{
  data: object
  tab?: string
  copyProducts?: {
    agent_id: number
    agents: { id: number, label: string, create_route: routeType }[]
    copy_route: routeType
  } | null
  copyTargets?: { id: number, agent_id: number, label: string, number_products: number }[]
  moveProducts?: MoveProducts | null
}>()

const moveRequest = ref<{ description: string; payload: Record<string, unknown> } | null>(null)
const selectedRecords = computed(() => ((props.data as any)?.data ?? []).filter((record: any) => selectedIds.value.includes(record.id)))
const canMoveSelection = computed(() => selectedRecords.value.length > 0 && selectedRecords.value.every((record: any) => record.is_generic))
function openMoveDialog() {
  moveRequest.value = {
    description: ctrans(":count products under a generic record", { count: String(selectedRecords.value.length) }),
    payload: { supplier_product_ids: selectedRecords.value.map((record: any) => record.supplier_product_id) },
  }
}

const _table = ref<InstanceType<typeof Table> | null>(null)
const selectedRows = ref<Record<string, boolean>>({})
const selectedIds = computed(() => Object.keys(selectedRows.value).filter((id) => selectedRows.value[id]).map(Number))

const isCopyDialogVisible = ref(false)
const isLoadingTargets = ref(false)
const isCopying = ref(false)
const targetSupplierId = ref<number | null>(null)
const copyError = ref<string | null>(null)
const targetAgentId = ref<number | null>(props.copyProducts?.agent_id ?? null)
const targetAgent = computed(() => props.copyProducts?.agents.find((agent) => agent.id === targetAgentId.value))
const targetSuppliers = computed(() => (props.copyTargets ?? []).filter((supplier) => supplier.agent_id === targetAgentId.value))

function openCopyDialog() {
  targetSupplierId.value = null
  copyError.value = null
  isCopyDialogVisible.value = true

  if (!props.copyTargets) {
    router.reload({
      only: ["copy_targets"],
      onStart: () => isLoadingTargets.value = true,
      onFinish: () => isLoadingTargets.value = false,
    })
  }
}

function copySelectedProducts() {
  if (!props.copyProducts || !targetSupplierId.value) {
    return
  }

  router.post(
    route(props.copyProducts.copy_route.name, [...Object.values(props.copyProducts.copy_route.parameters ?? {}), targetSupplierId.value]),
    { org_supplier_products: selectedIds.value },
    {
      preserveScroll: true,
      onStart: () => isCopying.value = true,
      onFinish: () => isCopying.value = false,
      onError: (errors) => copyError.value = Object.values(errors)[0] as string,
      onSuccess: () => {
        isCopyDialogVisible.value = false
        _table.value?.clearSelection()
        notify({
          title: ctrans("Products copied"),
          text: ctrans("The supplier now also sells the selected products"),
          type: "success",
        })
      },
    }
  )
}

function createSupplierWithSelectedProducts() {
  if (!targetAgent.value) {
    return
  }

  router.get(
    route(targetAgent.value.create_route.name, targetAgent.value.create_route.parameters),
    { org_supplier_products: selectedIds.value }
  )
}

const routeParams = route().params
const currentRouteName = route().current()

function addToShoppingList(supplierProduct: OrgSupplierProduct & { units_per_carton?: number }) {
  router.post(
    route("grp.org.procurement.shopping_list.store", [routeParams["organisation"], supplierProduct.slug]),
    { quantity_units: supplierProduct.units_per_carton ?? 1 },
    { preserveScroll: true }
  )
}

function supplierProductRoute(supplierProduct: OrgSupplierProduct) {
  switch (currentRouteName) {
    case "grp.org.procurement.org_agents.show.supplier_products.index":
      return route(
        "grp.org.procurement.org_agents.show.supplier_products.show",
        [routeParams["organisation"], routeParams["orgAgent"], supplierProduct.slug])

    case "grp.org.agent.org_supplier_products.index":
      return route(
        "grp.org.agent.org_supplier_products.show",
        [routeParams["organisation"], supplierProduct.slug])
    case "grp.org.procurement.org_suppliers.show":
    case "grp.org.procurement.org_suppliers.show.supplier_products.index":
      return route(
        "grp.org.procurement.org_suppliers.show.supplier_products.show",
        [routeParams["organisation"], routeParams["orgSupplier"], supplierProduct.slug])

    default:
      return route(
        "grp.org.procurement.org_supplier_products.show",
        [routeParams["organisation"], supplierProduct.slug])
  }
}

function orgSupplierRoute(orgSupplierSlug: string) {
  return route("grp.org.procurement.org_suppliers.show", [routeParams["organisation"], orgSupplierSlug])
}
</script>

<template>
  <div>
  <div v-if="copyProducts && selectedIds.length" class="mx-4 mt-4 flex flex-wrap items-center gap-2">
    <span class="text-sm text-gray-600">{{ ctrans(":count selected", { count: String(selectedIds.length) }) }}</span>
    <div class="relative">
      <Button type="tertiary" :label="ctrans('Add to another supplier')" @click="openCopyDialog">
        <template #icon>
          <FontAwesomeIcon icon="fal fa-copy" class="text-[--app-accent]" fixed-width aria-hidden="true" />
        </template>
      </Button>
      <div class="absolute top-0 right-0 text-[6px] rounded-full text-orange-500">
        <PingIcon v-if="copyProducts.agents.length > 0" />
      </div>
    </div>
    <div class="relative">
      <Button type="tertiary" :label="ctrans('New supplier with these products')" @click="copyProducts.agents.length > 1 ? openCopyDialog() : createSupplierWithSelectedProducts()">
        <template #icon>
          <FontAwesomeIcon icon="fal fa-plus" class="text-green-600" fixed-width aria-hidden="true" />
        </template>
      </Button>
      
      <div class="absolute top-0 right-0 text-[6px] rounded-full text-orange-500">
        <PingIcon v-if="copyProducts.agents.length > 0" />
      </div>
    </div>
  </div>
  <div v-if="moveProducts && selectedIds.length" class="mx-4 mt-4 flex flex-wrap items-center gap-2">
    <span class="text-sm text-gray-600">{{ ctrans(":count selected", { count: String(selectedIds.length) }) }}</span>
    <span v-tooltip="canMoveSelection ? undefined : ctrans('Only products under a generic record can be moved')">
      <Button type="tertiary" :label="ctrans('Move to the real factory')" :disabled="!canMoveSelection" @click="openMoveDialog">
        <template #icon>
          <FontAwesomeIcon icon="fal fa-truck-moving" class="text-green-600" fixed-width aria-hidden="true" />
        </template>
      </Button>
    </span>
  </div>
  <MoveToFactoryDialog v-if="moveProducts" :moveProducts="moveProducts" :request="moveRequest" @close="moveRequest = null" @moved="_table?.clearSelection()" />
  <Table
    ref="_table"
    :resource="data"
    :name="tab"
    class="mt-5"
    :isCheckBox="!!copyProducts || !!moveProducts"
    @onSelectRow="(rows: Record<string, boolean>) => selectedRows = { ...rows }"
  >
    <template #cell(code)="{ item: supplier_product }">
      <Link :href="supplierProductRoute(supplier_product)" class="primaryLink">
        {{ supplier_product["code"] }}
      </Link>
    </template>
    <template #cell(supplier_name)="{ item: supplier_product }">
      <Link
        v-if="supplier_product.org_supplier_slug"
        :href="orgSupplierRoute(supplier_product.org_supplier_slug)"
        class="secondaryLink">
        {{ supplier_product.supplier_name }}
      </Link>
      <span v-else>{{ supplier_product.supplier_name }}</span>
    </template>
    <template #cell(internal_code)="{ item: supplier_product }">
      <Link v-if="supplier_product.internal_code" :href="supplierProductRoute(supplier_product)" class="primaryLink">
        {{ supplier_product.internal_code }}
      </Link>
      <span v-else class="italic text-gray-400">{{ ctrans("No SKO") }}</span>
      <span v-if="supplier_product.availability_status && supplier_product.availability_status !== 'available'"
        class="ml-1.5 whitespace-nowrap rounded px-1 py-0.5 text-[11px]"
        :class="supplier_product.availability_status === 'limited' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700'">
        {{ supplier_product.availability_status === "limited" ? ctrans("Limited") : ctrans("Not available") }}
      </span>
    </template>
    <template v-for="key in ['cbm', 'lead_time_days', 'moq']" :key="key" #[`cell(${key})`]="{ item: supplier_product }">
      <span v-if="supplier_product[key] !== null && supplier_product[key] !== undefined">{{ supplier_product[key] }}</span>
      <span v-else class="text-gray-300">-</span>
    </template>
    <template #cell(completeness)="{ item: supplier_product }">
      <CompletenessBar :value="supplier_product.completeness" />
    </template>
    <template #cell(add)="{ item: supplier_product }">
      <button type="button" class="secondaryLink" @click="addToShoppingList(supplier_product)">
        {{ ctrans("Add to shopping list") }}
      </button>
    </template>
  </Table>

  <Dialog
    v-model:visible="isCopyDialogVisible"
    modal
    :header="ctrans('Add to another supplier')"
    :style="{ width: '30rem', maxWidth: 'calc(100vw - 2rem)' }"
    :draggable="false"
  >
    <div class="flex flex-col gap-2">
      <p class="text-sm text-gray-600">
        {{ ctrans(":count products stay with this supplier and are added to the other one, linked to the same SKOs. Products it already has are skipped.", { count: String(selectedIds.length) }) }}
      </p>
      <template v-if="copyProducts && copyProducts.agents.length > 1">
        <label for="copy-products-target-agent" class="font-medium text-gray-700">
          {{ ctrans("Agent") }}
        </label>
        <Select
          v-model="targetAgentId"
          inputId="copy-products-target-agent"
          :options="copyProducts.agents"
          optionLabel="label"
          optionValue="id"
          fluid
          @change="targetSupplierId = null"
        />
        <p v-if="targetAgentId !== copyProducts.agent_id" class="text-sm text-amber-600">
          {{ ctrans("Costs are converted to the supplier's currency at today's rate. Review them after adding.") }}
        </p>
      </template>
      <label for="copy-products-target-supplier" class="font-medium text-gray-700">
        {{ ctrans("Supplier") }}
      </label>
      <Select
        v-model="targetSupplierId"
        inputId="copy-products-target-supplier"
        :options="targetSuppliers"
        optionLabel="label"
        optionValue="id"
        filter
        fluid
        :loading="isLoadingTargets"
        :placeholder="ctrans('Select a supplier')"
        :emptyMessage="ctrans('No other supplier')"
      >
        <template #option="{ option }">
          <div class="flex w-full items-center justify-between gap-3">
            <span class="truncate">{{ option.label }}</span>
            <span class="shrink-0 text-xs tabular-nums text-gray-500">
              {{ ctransChoice(":count product|:count products", option.number_products, { count: String(option.number_products) }) }}
            </span>
          </div>
        </template>
      </Select>
      <p v-if="copyError" class="text-sm text-red-500">{{ copyError }}</p>
    </div>

    <template #footer>
      <Button type="tertiary" :label="ctrans('Cancel')" @click="isCopyDialogVisible = false">
        <template #icon>
          <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
        </template>
      </Button>
      <Button type="tertiary" :label="ctrans('New supplier')" :disabled="!targetAgent" @click="createSupplierWithSelectedProducts">
        <template #icon>
          <FontAwesomeIcon icon="fal fa-plus" class="text-green-600" fixed-width aria-hidden="true" />
        </template>
      </Button>
      <Button type="tertiary" :label="ctrans('Add')" :disabled="!targetSupplierId" :loading="isCopying" @click="copySelectedProducts">
        <template #icon>
          <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
        </template>
      </Button>
    </template>
  </Dialog>
  </div>
</template>
