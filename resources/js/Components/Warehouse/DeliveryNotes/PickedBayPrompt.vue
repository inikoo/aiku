<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 22 Oct 2026 17:20:00 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { onBeforeUnmount, ref } from "vue"
import { router } from "@inertiajs/vue3"
import axios from "axios"
import Dialog from "primevue/dialog"
import Select from "primevue/select"
import { notify } from "@kyvg/vue3-notification"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheck, faExchange, faMapMarkerAlt, faCheckCircle } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { RouteParams } from "@/types/route-params"

library.add(faCheck, faExchange, faMapMarkerAlt, faCheckCircle)

interface PickedBayPrompt {
    delivery_note: { id: number, slug: string, reference: string, is_ready: boolean }
    org_stock_code: string | null
    picked_bays: { id: number, code: string }[]
}

const prompt = ref<PickedBayPrompt | null>(null)
const isOpen = ref(false)
const isChangingBay = ref(false)
const availableBays = ref<{ id: number, code: string }[]>([])
const isLoadingBays = ref(false)
const isSubmittingBay = ref(false)

const loadAvailableBays = async () => {
    isLoadingBays.value = true
    try {
        const response = await axios.get(route("grp.json.picked_bays.list", {
            organisation: (route().params as RouteParams).organisation,
            warehouse: (route().params as RouteParams).warehouse,
        }))
        availableBays.value = response.data.data
    } catch (error: any) {
        notify({ title: ctrans("Something went wrong"), text: error.message, type: "error" })
    } finally {
        isLoadingBays.value = false
    }
}

const startChangingBay = () => {
    isChangingBay.value = true
    loadAvailableBays()
}

const stopListening = router.on("flash", (event) => {
    const flashedPrompt = event.detail.flash?.picked_bay_prompt as PickedBayPrompt | undefined
    if (!flashedPrompt) {
        return
    }
    prompt.value = flashedPrompt
    isOpen.value = true
    isChangingBay.value = false
    if (!flashedPrompt.picked_bays.length) {
        startChangingBay()
    }
})
onBeforeUnmount(stopListening)

const moveToBay = (bayId: number) => {
    if (!prompt.value) {
        return
    }
    router.patch(
        route("grp.models.delivery_note.state.change_picked_bay", { deliveryNote: prompt.value.delivery_note.id }),
        { picked_bay: bayId },
        {
            preserveScroll: true,
            preserveState: true,
            onStart: () => { isSubmittingBay.value = true },
            onSuccess: () => { isOpen.value = false },
            onError: () => notify({ title: ctrans("Something went wrong"), text: ctrans("Failed to submit picked bay"), type: "error" }),
            onFinish: () => { isSubmittingBay.value = false },
        }
    )
}
</script>

<template>
    <Dialog v-model:visible="isOpen" modal :header="ctrans('Take it to the picked bay')" :style="{ width: '28rem' }" :breakpoints="{ '640px': '92vw' }">
        <div v-if="prompt">
            <p class="text-sm text-gray-600">
                {{ ctrans(":code is complete for delivery note :reference. Take it to:", { code: prompt.org_stock_code ?? '', reference: prompt.delivery_note.reference }) }}
            </p>

            <div v-if="prompt.picked_bays.length" class="my-4 flex items-center justify-center gap-x-2 text-3xl font-bold whitespace-nowrap">
                <FontAwesomeIcon icon="fal fa-map-marker-alt" class="text-gray-400" fixed-width aria-hidden="true" />
                {{ prompt.picked_bays.map(bay => bay.code).join(", ") }}
            </div>
            <p v-else class="my-4 text-center text-sm text-amber-600">
                {{ ctrans("This delivery note has no picked bay yet: select where you leave it") }}
            </p>

            <p v-if="prompt.delivery_note.is_ready" class="mb-4 text-sm text-green-600">
                <FontAwesomeIcon icon="fal fa-check-circle" fixed-width aria-hidden="true" />
                {{ ctrans("Nothing else is missing: the order is ready to pack") }}
            </p>

            <Select
                v-if="isChangingBay"
                :options="availableBays"
                optionLabel="code"
                optionValue="id"
                :optionDisabled="(bay) => prompt?.picked_bays.some(current => current.id === bay.id) ?? false"
                :loading="isLoadingBays"
                :disabled="isSubmittingBay"
                filter
                :placeholder="ctrans('Select a picked bay')"
                class="mb-4 w-full"
                @update:modelValue="moveToBay"
            />

            <div class="flex justify-end gap-x-2">
                <Button v-if="!isChangingBay" type="tertiary" :label="ctrans('Change bay')" @click="startChangingBay">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-exchange" class="text-amber-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
                <Button type="tertiary" :label="ctrans('Confirm')" :disabled="!prompt.picked_bays.length" @click="isOpen = false">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </div>
    </Dialog>
</template>
