<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { router } from "@inertiajs/vue3"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import Dialog from "primevue/dialog"
import Select from "primevue/select"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faTruckMoving, faTimes } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"

library.add(faTruckMoving, faTimes)

export type MoveProducts = {
    targets: { id: number; code: string; name: string }[]
    route_name: string
    organisation_id: number
}

const props = defineProps<{
    moveProducts: MoveProducts
    request: { description: string; payload: Record<string, unknown>; excludeSupplierId?: number } | null
}>()

const emit = defineEmits<{ (e: "close"): void; (e: "moved"): void }>()

const visible = computed({
    get: () => !!props.request,
    set: (value) => {
        if (!value) emit("close")
    },
})

const targetId = ref<number | null>(null)
const error = ref<string | null>(null)
const isMoving = ref(false)
watch(() => props.request, () => {
    targetId.value = null
    error.value = null
})

const targets = computed(() =>
    props.moveProducts.targets
        .filter((target) => target.id !== props.request?.excludeSupplierId)
        .map((target) => ({ value: target.id, label: `${target.code} · ${target.name}` }))
)

const move = async () => {
    if (!targetId.value || !props.request || isMoving.value) return

    isMoving.value = true
    error.value = null
    try {
        const { data } = await axios.post(route(props.moveProducts.route_name, { organisation: props.moveProducts.organisation_id, supplier: targetId.value }), props.request.payload)
        notify({
            title: ctrans("Products moved"),
            text: data.skipped?.length
                ? ctrans(":moved moved; :skipped left behind, the factory already has their codes: :codes", { moved: data.moved, skipped: data.skipped.length, codes: data.skipped.join(", ") })
                : ctrans(":moved moved, their purchase order history comes with them", { moved: data.moved }),
            type: "success",
        })
        emit("moved")
        emit("close")
        router.reload()
    } catch (exception: any) {
        const errors = exception?.response?.data?.errors
        error.value = errors ? (Object.values(errors).flat().join(" ") as string) : ctrans("Could not move the products")
    } finally {
        isMoving.value = false
    }
}
</script>

<template>
    <Dialog v-model:visible="visible" modal :header="ctrans('Move to the real factory')" :style="{ width: '30rem', maxWidth: 'calc(100vw - 2rem)' }">
        <div class="space-y-3">
            <p class="text-sm text-gray-600">{{ request?.description }}</p>
            <Select v-model="targetId" :options="targets" optionLabel="label" optionValue="value" filter :placeholder="ctrans('Factory')"
                class="w-full [&.p-focus]:!border-[--app-accent]" />
            <p class="text-xs text-gray-500">
                {{ ctrans("Each product keeps its purchase order lines, deliveries and history. Past purchase orders stay on the generic record.") }}
            </p>
            <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
        </div>
        <template #footer>
            <Button type="tertiary" :label="ctrans('Cancel')" @click="emit('close')">
                <template #icon><FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" /></template>
            </Button>
            <Button type="tertiary" :label="ctrans('Move')" :loading="isMoving" :disabled="!targetId" @click="move">
                <template #icon><FontAwesomeIcon icon="fal fa-truck-moving" class="text-green-600" fixed-width aria-hidden="true" /></template>
            </Button>
        </template>
    </Dialog>
</template>
