<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, watch } from "vue"
import Dialog from "primevue/dialog"
import InputNumber from "primevue/inputnumber"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { faCheck, faTimes } from "@fal"

type SupplierMinimum = { id: number, code: string, name: string, supplier_name: string | null, minimum_carton_order: number | null }

const props = defineProps<{ product: SupplierMinimum | null }>()
const visible = defineModel<boolean>("visible", { required: true })
const emit = defineEmits<{ saved: [] }>()

const rows = ref<SupplierMinimum[]>([])
const minimums = ref<Record<number, number | null>>({})
const isLoading = ref(false)
const isSaving = ref(false)

watch(visible, async (isOpen) => {
    if (!isOpen || !props.product) {
        return
    }
    rows.value = [props.product]
    minimums.value = { [props.product.id]: props.product.minimum_carton_order }
    isLoading.value = true
    try {
        const response = await axios.get(route("grp.json.org_supplier_product.minimum_orders", { orgSupplierProduct: props.product.id }))
        rows.value = response.data
        minimums.value = Object.fromEntries(rows.value.map((row) => [row.id, row.id in minimums.value ? minimums.value[row.id] : row.minimum_carton_order]))
    } catch {
        notify({ title: ctrans("Something went wrong"), text: ctrans("The other suppliers of this product could not be loaded"), type: "error" })
    } finally {
        isLoading.value = false
    }
})

const save = async () => {
    const changed = rows.value.filter((row) => (minimums.value[row.id] ?? null) !== row.minimum_carton_order)
    if (!changed.length) {
        visible.value = false
        return
    }
    isSaving.value = true
    try {
        await Promise.all(changed.map((row) => axios.patch(
            route("grp.models.org_supplier_product.minimum_carton_order.update", { orgSupplierProduct: row.id }),
            { minimum_carton_order: minimums.value[row.id] ?? null }
        )))
        notify({ title: ctrans("Saved"), text: ctrans("The minimum order is updated for every organisation"), type: "success" })
        visible.value = false
        emit("saved")
    } catch {
        notify({ title: ctrans("Something went wrong"), text: ctrans("The minimum order could not be saved"), type: "error" })
    } finally {
        isSaving.value = false
    }
}
</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        dismissableMask
        :closable="false"
        :style="{ width: '30rem', maxWidth: 'calc(100vw - 2rem)' }"
        :pt="{ header: { class: '!px-4 !py-2.5 border-b border-gray-200' }, content: { class: '!p-4' } }"
    >
        <template #header>
            <div class="flex w-full items-center justify-between gap-3">
                <span class="text-sm font-semibold text-gray-900">{{ ctrans("Minimum order (cartons)") }}</span>
                <button type="button" class="text-gray-400 hover:text-gray-700" :aria-label="ctrans('Close')" @click="visible = false">
                    <FontAwesomeIcon :icon="faTimes" fixed-width aria-hidden="true" />
                </button>
            </div>
        </template>

        <div class="space-y-3 text-sm">
            <p class="text-gray-500">{{ ctrans("The minimum is the supplier's, so it applies to every organisation ordering from it. Leave empty when there is no minimum.") }}</p>
            <div class="divide-y divide-gray-100">
                <div v-for="row in rows" :key="row.id" class="flex items-center justify-between gap-3 py-2">
                    <div class="min-w-0">
                        <div class="font-medium text-gray-900">{{ row.supplier_name ?? ctrans("Unknown supplier") }}</div>
                        <div class="truncate text-xs text-gray-500">{{ row.code }} · {{ row.name }}</div>
                    </div>
                    <InputNumber v-model="minimums[row.id]" :min="1" :useGrouping="false" :placeholder="ctrans('None')" :suffix="` ${ctrans('ctn')}`" inputClass="!w-24 !py-1 text-right" />
                </div>
                <div v-if="isLoading" class="h-9 animate-pulse rounded bg-gray-100" />
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <Button type="tertiary" size="xs" :label="ctrans('Cancel')" @click="visible = false">
                    <template #icon>
                        <FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
                <Button type="tertiary" size="xs" :label="ctrans('Save')" :loading="isSaving" @click="save">
                    <template #icon>
                        <FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </div>
    </Dialog>
</template>
