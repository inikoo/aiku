<script setup lang='ts'>
import { routeType } from '@/types/route'
import { notify } from '@kyvg/vue3-notification'
import { trans } from 'laravel-vue-i18n'
import { ref } from 'vue'
import Button from '../Elements/Buttons/Button.vue'
import { PalletDelivery } from '@/types/Pallet'
import { router } from '@inertiajs/vue3'
import Modal from '../Utils/Modal.vue'
import LoadingIcon from '../Utils/LoadingIcon.vue'
import PureInput from '@/Components/Pure/PureInput.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faLevelDown } from '@fas'
library.add(faLevelDown)


const props = defineProps<{
    dataPalletDelivery: PalletDelivery
    updateRoute: routeType

}>()

const cloneCustomerReference = ref(props.dataPalletDelivery.customer_reference)
const showModalEdit = ref(false)
const isLoadingSaveCustomer = ref(false)
const onUpdateCustomerReference = () => {
    // console.log('eee', cloneCustomerReference.value)
    router.patch(route(props.updateRoute.name, props.updateRoute.parameters),
    {
        customer_reference: cloneCustomerReference.value
    },
    {
        onStart: () => isLoadingSaveCustomer.value = true,
        onError: () => {
            notify({
                title: trans("Failed"),
                text: trans("Failed to update the Customer reference, try again."),
                type: "error",
            })
        },
        onSuccess: () => {
            showModalEdit.value = false,
            notify({
                title: trans("Success"),
                text: trans("Customer reference updated successfully."),
                type: "success",
            }),
            cloneCustomerReference.value = props.dataPalletDelivery.customer_reference
        },
        onFinish: () => isLoadingSaveCustomer.value = false,
    })
}
</script>

<template>
    <div @click="showModalEdit = !showModalEdit" class="flex gap-x-1 items-center truncate">
        <FontAwesomeIcon icon='fal fa-hashtag' class='text-gray-400' fixed-width aria-hidden='true' />
        <template v-if="!dataPalletDelivery.customer_reference">
            <Button type="dashed" size="xs"><span class="text-gray-400">{{ trans("Edit Customer reference") }}</span></Button>
        </template>

        <div v-else class="group border-b border-dashed border-gray-400 hover:border-solid hover:border-gray-500 cursor-pointer">
            <span class="text-gray-500 truncate">{{ dataPalletDelivery.customer_reference }}</span>
            <FontAwesomeIcon icon='fal fa-pencil' size="xs" class='ml-1 text-gray-400 group-hover:text-gray-600' fixed-width aria-hidden='true' />
        </div>

        
        <Modal :isOpen="showModalEdit" @onClose="showModalEdit = false" width="w-full max-w-lg">
            <h2 class="text-lg font-semibold mb-2">{{ trans("Edit Customer Reference") }}</h2>
            <div class="flex gap-x-2">
                <PureInput
                    v-model="cloneCustomerReference"
                    placeholder="PD-2025-1234"
                    size="sm"
                    @onEnter="onUpdateCustomerReference"
                />

                <Button type="primary" @click="onUpdateCustomerReference" :loading="isLoadingSaveCustomer">
                    <LoadingIcon v-if="isLoadingSaveCustomer" />
                    {{ trans('Save') }}
                    <FontAwesomeIcon icon='fas fa-level-down' class='rotate-90' fixed-width aria-hidden='true' />
                </Button>
            </div>
        </Modal>
    </div>
</template>