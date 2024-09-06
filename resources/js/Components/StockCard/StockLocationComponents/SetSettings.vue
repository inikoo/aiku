<script setup lang="ts">
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { Datum } from "@/types/StockLocation"
import Button from "@/Components/Elements/Buttons/Button.vue"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faShoppingBasket } from '@far'
import { faUnlink } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'

library.add(faShoppingBasket, faUnlink)

const props = defineProps<{
    data: Datum
    close: Function
}>();

const emits = defineEmits<{
    (e: 'submitSetting', value: Record<string, any>): void
}>()

const loading = ref(false)

const form = useForm({
    settings : {
        min_stock: props.data.settings.min_stock,
        max_stock: props.data.settings.max_stock,
    },
    id: props.data.id
})

watch(() => props.data, (newData) => {
  form.settings.min_stock = newData.settings.min_stock
  form.settings.max_stock = newData.settings.max_stock
  form.id = newData.id
}, { deep: true })

const submitForm = () => {
    if (form.settings.min_stock > form.settings.max_stock) {
        form.errors.settings = {
            ...form.errors.settings,
            min_stock: 'Min stock cannot be greater than Max stock.'
        }
        return
    }

    // Clear error messages if validation passes
    form.errors.settings = {
        ...form.errors.settings,
        min_stock: ''
    }
    emits('submitSetting', { settingForm: form, loading: loading, close: props.close() })
}
</script>

<template>
    <div>
        <div class="grid grid-cols-2 gap-4 pb-2">
            <div>
                <span>Min</span>
                <PureInputNumber v-model="form.settings.min_stock" autofocus placeholder="Min" :minValue="0" />
                <p class="text-xs text-red-500">{{ form.errors.settings?.min_stock }}</p>
            </div>
            <div>
                <span>Max</span>
                <PureInputNumber v-model="form.settings.max_stock" autofocus placeholder="Max" :minValue="0" />
                <p class="text-xs text-red-500">{{ form.errors.settings?.max_stock }}</p>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <Button size="xs" type="gray" label="Cancel" @click="close()" />
            <Button size="xs" type="save" :loading="loading" label="Save" @click="submitForm" />
        </div>
    </div>
</template>
