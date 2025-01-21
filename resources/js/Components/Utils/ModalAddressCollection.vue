<script setup lang="ts">
import PureAddress from '@/Components/Pure/PureAddress.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { Link, router } from '@inertiajs/vue3'
import { notify } from '@kyvg/vue3-notification'
import { ref } from 'vue'
import { routeType } from '@/types/route'
import { trans } from 'laravel-vue-i18n'
import { Address, AddressManagement } from "@/types/PureComponent/Address"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faThumbtack, faPencil, faHouse, faTrashAlt, faTruck, faTruckCouch } from '@fal'
import { faCheckCircle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import { useTruncate } from '@/Composables/useTruncate'
library.add(faThumbtack, faPencil, faHouse, faTrashAlt, faTruck, faTruckCouch, faCheckCircle)

const props = defineProps<{
    updateRoute: routeType
    addresses: AddressManagement
    keyPayloadEdit?: string
}>()

// const emits = defineEmits<{
//     (e: 'setModal', value: boolean): void
// }>()
// console.log('address list', props.addresses.address_list)
const homeAddress = props.addresses.address_list?.data.find(address => address.id === props.addresses.home_address_id)


// Method: Create new address
const isSubmitAddressLoading = ref(false)
const onSubmitNewAddress = async (address: Address) => {
    // console.log(props.addresses.value)
    const filterDataAdddress = {...address}
    delete filterDataAdddress.formatted_address
    delete filterDataAdddress.country
    delete filterDataAdddress.id  // Remove id cuz create new one

    router[props.addresses.routes_list.store_route.method || 'post'](
        route(props.addresses.routes_list.store_route.name, props.addresses.routes_list.store_route.parameters),
        {
            delivery_address: filterDataAdddress
        },
        {
            preserveScroll: true,
            onStart: () => isSubmitAddressLoading.value = true,
            onFinish: () => {
                isSubmitAddressLoading.value = false,
                isCreateNewAddress.value = false
                // isModalAddress.value = false
                // emits('setModal', false)
            },
            onSuccess: () => {
                notify({
                    title: trans("Success"),
                    text: trans("Successfully create new address."),
                    type: "success",
                })
            },
            onError: () => notify({
                title: trans("Failed"),
                text: trans("Failed to submit the address, try again"),
                type: "error",
            })
        }
    )

}

// Method: Edit address history
const isEditAddress = ref(false)
const selectedAddress = ref<Address | { country_id: null }>({
    country_id: null
})
const onEditAddress = (address: Address) => {
    isEditAddress.value = true
    selectedAddress.value = {...address}
}
const onSubmitEditAddress = (address: Address) => {
    // console.log(props.addresses.value)
    const filterDataAdddress = {...address}
    delete filterDataAdddress.formatted_address
    delete filterDataAdddress.country
    delete filterDataAdddress.country_code

    router.patch(
        route(props.updateRoute.name, props.updateRoute.parameters),
        {
            [props.keyPayloadEdit || 'address']: filterDataAdddress
        },
        {
            preserveScroll: true,
            onStart: () => isSubmitAddressLoading.value = true,
            onFinish: () => {
                isSubmitAddressLoading.value = false
                isCreateNewAddress.value = false
                // isModalAddress.value = false
            },
            onSuccess: () => {
                notify({
                    title: trans("Success"),
                    text: trans("Successfully update the address."),
                    type: "success",
                })
            },
            onError: () => notify({
                title: trans("Failed"),
                text: trans("Failed to update the address, try again."),
                type: "error",
            })
        }
    )
}

// Method: Select address history
const isCreateNewAddress = ref(false)
const isSelectAddressLoading = ref<number | boolean | null | undefined>(false)
const onSelectAddress = (selectedAddress: Address) => {
    router.patch(
        route(props.updateRoute.name, props.updateRoute.parameters),
        {
            delivery_address_id: selectedAddress.id
        },
        {
            onStart: () => isSelectAddressLoading.value = selectedAddress.id,
            onFinish: () => isSelectAddressLoading.value = false
        }
    )
    // props.addresses.value = selectedAddress
}

const isLoading = ref<string | boolean>(false)
// Method: Pinned address
const onPinnedAddress = (addressID: number) => {
    router[props.addresses.routes_list.pinned_route.method || 'patch'](
        route(props.addresses.routes_list.pinned_route.name, props.addresses.routes_list.pinned_route.parameters),
        {
            delivery_address_id: addressID
        },
        {
            preserveScroll: true,
            onStart: () => isLoading.value = 'onPinned' + addressID,
            onFinish: () => {
                isLoading.value = false
            },
            onError: () => notify({
                title: trans("Failed"),
                text: "Failed to pinned the address, try again.",
                type: "error",
            })
        }
    )
}
// Method: Delete address
const onDeleteAddress = (addressID: number) => {
    // console.log('vvcxvcxvcx', props.addressesList.delete_route.method, route(props.addressesList.delete_route.name, props.addressesList.delete_route.parameters))
    router.delete(
        route(props.addresses.routes_list.delete_route.name, {
            ...props.addresses.routes_list.delete_route.parameters,
            address: addressID
        }),
        {
            preserveScroll: true,
            onStart: () => isLoading.value = 'onDelete' + addressID,
            onFinish: () => {
                isLoading.value = false
            },
            onError: () => notify({
                title: trans("Failed"),
                text: trans("Failed to delete the address, try again"),
                type: "error",
            })
        }
    )
}


</script>

<template>
    <div class="h-[600px] px-2 py-1 overflow-auto">
    <!-- <pre>current selected {{ addresses.current_selected_address_id }}</pre>
    <pre>pinned address {{ addresses.pinned_address_id }}</pre>
    <pre>home {{ addresses.home_address_id }}</pre> -->
        <div class="flex justify-between border-b border-gray-300">
            <div class="text-2xl font-bold text-center mb-2 flex gap-x-2">
                {{ trans('Address Collection') }}

                <div class="relative">
                    <Transition name="slide-to-right">
                        <div v-if="isEditAddress" class="inline text-gray-400 italic text-base font-normal">({{ trans('Edit') }})</div>
                        <div v-else-if="isCreateNewAddress" class="inline text-gray-400 italic text-base font-normal">({{ trans('Create new') }})</div>
                        <div v-else></div>
                    </Transition>
                </div>
            </div>
            
            <div class="flex gap-x-2 h-fit">
                <Button v-if="isCreateNewAddress || isEditAddress" type="cancel" @click="() => (selectedAddress = {country_id: null}, isCreateNewAddress = false, isEditAddress = false)"></Button>
                <Button v-else label="New address" type="create" @click="() => (selectedAddress = {country_id: null}, isCreateNewAddress = true)"></Button>
            </div>
        </div>

        <div class="relative transition-all">
       
        </div>

    </div>
</template>

<style scoped></style>