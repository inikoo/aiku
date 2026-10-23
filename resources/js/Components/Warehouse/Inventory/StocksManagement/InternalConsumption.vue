<script setup lang="ts">
import { ctrans } from '@/Composables/useTrans'
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { notify } from '@kyvg/vue3-notification'
import { InputNumber, InputText, Select } from 'primevue'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faBoxOpen, faCheck, faTimes } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import Button from '@/Components/Elements/Buttons/Button.vue'
import { StockLocation } from '@/types/Inventory/StocksManagement'
library.add(faBoxOpen, faCheck, faTimes)

const props = defineProps<{
    locations: StockLocation[]
}>()

const emits = defineEmits(['close'])

const locationsWithStock = computed(() =>
    [...props.locations].sort((a, b) => Number(b.quantity) - Number(a.quantity))
)

const selectedLocationId = ref<number | null>(locationsWithStock.value[0]?.id ?? null)
const selectedLocation = computed(() => props.locations.find(location => location.id === selectedLocationId.value))
const quantity = ref<number | null>(null)
const note = ref('')
const isSubmitting = ref(false)
const quantityError = ref<string | null>(null)

const canSubmit = computed(() =>
    !!selectedLocation.value
    && !!quantity.value
    && quantity.value > 0
    && quantity.value <= Number(selectedLocation.value.quantity)
)

const submit = () => {
    if (!canSubmit.value || !selectedLocation.value) {
        return
    }

    router.patch(
        route('grp.models.location_org_stock.consume', { locationOrgStock: selectedLocation.value.id }),
        { quantity: quantity.value, note: note.value || null },
        {
            preserveScroll: true,
            onStart: () => {
                isSubmitting.value = true
                quantityError.value = null
            },
            onSuccess: () => {
                notify({
                    title: ctrans('Success'),
                    text: ctrans(':quantity consumed from :location', {
                        quantity: String(quantity.value),
                        location: selectedLocation.value?.code ?? '',
                    }),
                    type: 'success',
                })
                emits('close')
            },
            onError: (errors) => {
                quantityError.value = errors.quantity ?? null
                notify({
                    title: ctrans('Something went wrong'),
                    text: errors.quantity ?? ctrans('Failed to record the internal consumption'),
                    type: 'error',
                })
            },
            onFinish: () => {
                isSubmitting.value = false
            },
        }
    )
}
</script>

<template>
    <div class="flex flex-col gap-4">
        <div v-if="locations.length > 1" class="flex flex-col gap-1">
            <label class="text-sm text-gray-600">{{ ctrans('Location') }}</label>
            <Select
                v-model="selectedLocationId"
                :options="locationsWithStock"
                optionValue="id"
                :optionLabel="(location: StockLocation) => `${location.code} (${Number(location.quantity)})`"
                class="w-full"
            />
        </div>
        <div v-else-if="selectedLocation" class="text-sm text-gray-600">
            {{ ctrans('Location') }}: <span class="font-semibold whitespace-nowrap">{{ selectedLocation.code }}</span>
            ({{ Number(selectedLocation.quantity) }} {{ ctrans('in stock') }})
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-sm text-gray-600">{{ ctrans('Quantity consumed') }}</label>
            <InputNumber
                v-model="quantity"
                :min="0"
                :max="selectedLocation ? Number(selectedLocation.quantity) : undefined"
                :maxFractionDigits="3"
                autofocus
                class="w-full"
                inputClass="w-full"
                @keydown.enter="submit"
            />
            <small v-if="quantityError" class="text-red-500">{{ quantityError }}</small>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-sm text-gray-600">{{ ctrans('Note') }} <span class="text-gray-400">({{ ctrans('optional') }})</span></label>
            <InputText v-model="note" class="w-full" :placeholder="ctrans('e.g. used for packing')" />
        </div>

        <div class="flex justify-end gap-2">
            <Button type="tertiary" :label="ctrans('Cancel')" @click="emits('close')">
                <template #icon>
                    <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
                </template>
            </Button>
            <Button type="tertiary" :label="ctrans('Consume')" :disabled="!canSubmit" :loading="isSubmitting" @click="submit">
                <template #icon>
                    <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                </template>
            </Button>
        </div>
    </div>
</template>
