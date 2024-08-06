<script setup lang="ts">
import PureAddress from '@/Components/Pure/PureAddress.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { Link, router } from '@inertiajs/vue3'
import { notify } from '@kyvg/vue3-notification'
import { ref } from 'vue'
import { routeType } from '@/types/route'
import { trans } from 'laravel-vue-i18n'
import { Address, AddressOptions } from "@/types/PureComponent/Address"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faThumbtack, faPencil, faHouse, faTrashAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import { useTruncate } from '../../Composables/useTruncate'
library.add(faThumbtack, faPencil, faHouse, faTrashAlt)

const props = defineProps<{
    updateRoute: routeType
    addressCustomer: {
        value: Address
        options: AddressOptions
    }
    addressList: {
        pinned_address_id: number
        home_address_id: number
        current_selected_address_id: number
        all_addresses: {
            data: Address[]
        }
        pinned_route: routeType
        delete_route: routeType
    }
}>()

const emits = defineEmits<{
    (e: 'setModal', value: boolean): void
}>()

const homeAddress = props.addressList.all_addresses.data.find(address => address.id === props.addressList.home_address_id)


// Method: Create new address
const isSubmitAddressLoading = ref(false)
const onSubmitNewAddress = async (address: Address) => {
    // console.log(props.address.value)
    const filterDataAdddress = {...address}
    delete filterDataAdddress.formatted_address
    delete filterDataAdddress.country
    delete filterDataAdddress.id  // Remove id cuz create new one

    router.patch(
        route(props.updateRoute.name, props.updateRoute.parameters),
        {
            address: filterDataAdddress
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
            onError: () => notify({
                title: "Failed",
                text: "Failed to update the address, try again.",
                type: "error",
            })
        }
    )

}

// Method: Edit address history
const isEditAddress = ref(false)
const selectedAddress = ref<Address | null | { country_id: null }>({
    country_id: null
})
const onEditAddress = (address: Address) => {
    isEditAddress.value = true
    selectedAddress.value = {...address}
}
const onSubmitEditAddress = (address: Address) => {
    // console.log(props.address.value)
    const filterDataAdddress = {...address}
    delete filterDataAdddress.formatted_address
    delete filterDataAdddress.country
    delete filterDataAdddress.country_code

    router.patch(
        route(props.updateRoute.name, props.updateRoute.parameters),
        {
            address: filterDataAdddress
        },
        {
            preserveScroll: true,
            onStart: () => isSubmitAddressLoading.value = true,
            onFinish: () => {
                isSubmitAddressLoading.value = false
                isCreateNewAddress.value = false
                // isModalAddress.value = false
            },
            onError: () => notify({
                title: "Failed",
                text: "Failed to update the address, try again.",
                type: "error",
            })
        }
    )
}

// Method: Select address history
const isCreateNewAddress = ref(false)
const isSelectAddressLoading = ref<number | boolean>(false)
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
    // props.address.value = selectedAddress
}

const isLoading = ref<string | boolean>(false)
const onPinnedAddress = (addressID: number) => {
    router[props.addressList.pinned_route.method || 'patch'](
        route(props.addressList.pinned_route.name, props.addressList.pinned_route.parameters),
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
                title: "Failed",
                text: "Failed to pinned the address, try again.",
                type: "error",
            })
        }
    )
}
const onDeleteAddress = (addressID: number) => {
    // console.log('vvcxvcxvcx', props.addressList.delete_route.method, route(props.addressList.delete_route.name, props.addressList.delete_route.parameters))
    router[props.addressList.delete_route.method || 'delete'](
        route(props.addressList.delete_route.name, {
            ...props.addressList.delete_route.parameters,
            address: addressID
        }),
        {
            preserveScroll: true,
            onStart: () => isLoading.value = 'onDelete' + addressID,
            onFinish: () => {
                isLoading.value = false
            },
            onError: () => notify({
                title: "Failed",
                text: trans("Failed to delete the address, try again"),
                type: "error",
            })
        }
    )
}


</script>

<template>
    <div class="h-[600px] px-2 py-1 overflow-auto">
    <!-- <pre>{{ addressList.all_addresses.data }}</pre> -->
        <div class="flex justify-between border-b border-gray-300">
            <div class="text-2xl font-bold text-center mb-2">
                {{ trans('Address management') }}

                <div v-if="isEditAddress" class="inline text-gray-400 italic text-base font-normal">(Edit)</div>
                <div v-if="isCreateNewAddress" class="inline text-gray-400 italic text-base font-normal">(Create new)</div>
            </div>
            
            <div class="flex gap-x-2 h-fit">
                <Button v-if="isCreateNewAddress || isEditAddress" type="cancel" @click="() => (selectedAddress = {country_id: null}, isCreateNewAddress = false, isEditAddress = false)"></Button>
                <Button v-else label="New address" type="create" @click="() => (selectedAddress = {country_id: null}, isCreateNewAddress = true)"></Button>
            </div>
        </div>

        <div class="relative">
            <Transition name="slide-to-left">
                <div v-if="isCreateNewAddress" class="max-w-96 mx-auto py-4">
                    <div class="mb-2">{{ trans('Create new address')}} </div>
                    <div class="border border-gray-300 rounded-lg relative p-3 ">
                        <PureAddress
                            v-model="selectedAddress"
                            :options="addressCustomer.options"
                            fieldLabel
                        />
                        <div class="mt-6 flex justify-center gap-x-2">
                            <Button label="Cancel" type="cancel" @click="() => isCreateNewAddress = false" />
                            <Button
                                @click="() => onSubmitNewAddress(selectedAddress)"
                                label="Create new and select"
                                :loading="isSubmitAddressLoading"
                                full
                                :disabled="!selectedAddress?.country_id"    
                            />
                        </div>
                        <!-- <Transition>
                                <div class="absolute inset-0 bg-black/30 text-white text-lg rounded-md grid place-content-center">
                                    Not editable
                                </div>
                            </Transition> -->
                    </div>
                </div>

                <!-- Form: Edit address -->
                <div v-else-if="isEditAddress" :key="'edit' + selectedAddress?.id" class="col-span-2 relative py-4 h-fit grid grid-cols-2 gap-x-4">
                    <div class="overflow-hidden relative text-xs ring-1 ring-gray-300 rounded-lg h-fit transition-all"
                        :class="[
                            selectedAddress?.id ? 'ring-2 ring-offset-4 ring-indigo-500' : ''
                        ]"
                    >
                        <div class="flex justify-between border-b border-gray-300 px-3 py-2"
                            :class="addressList.current_selected_address_id == selectedAddress?.id ? 'bg-green-50' : 'bg-gray-100'"
                        >
                            <div class="flex gap-x-1 items-center relative">
                                <div v-if="selectedAddress?.label" class="font-semibold text-sm whitespace-nowrap">
                                    {{ [...addressList.all_addresses.data].find(xxx => xxx.id === selectedAddress?.id)?.label }}
                                </div>
                                <div v-else class="text-xs italic whitespace-nowrap text-gray-400">
                                    (No label)
                                </div>
                                <div class="relative">
                                    <Transition name="slide-to-left">
                                        <FontAwesomeIcon v-if="addressList.current_selected_address_id == selectedAddress?.id" icon='fas fa-check-circle' class='text-green-500' fixed-width aria-hidden='true' />
                                        <Button
                                            v-else
                                            @click="() => onSelectAddress(selectedAddress)"
                                            :label="isSelectAddressLoading == selectedAddress?.id ? '' : 'Select'"
                                            size="xxs"
                                            type="tertiary"
                                            :loading="isSelectAddressLoading == selectedAddress?.id"
                                        />
                                    </Transition>
                                </div>
                            </div>
                        </div>

                        <div v-html="selectedAddress?.formatted_address" class="px-3 py-2"></div>
                    </div>

                    <!-- Form: Edit address -->
                    <div class="relative bg-gray-100 p-4 rounded-md">
                        <div @click="() => (isEditAddress = false, selectedAddress = null)"
                            class="absolute top-2 right-2 cursor-pointer">
                            <FontAwesomeIcon icon='fal fa-times' class='text-gray-400 hover:text-gray-500'
                                fixed-width aria-hidden='true' />
                        </div>
                        <PureAddress
                            v-model="selectedAddress"
                            :options="addressCustomer.options"
                            fieldLabel
                        />
                        <div class="mt-6 flex justify-center">
                            <Button
                                @click="() => onSubmitEditAddress(selectedAddress)"
                                label="Edit address"
                                :loading="isSubmitAddressLoading"
                                full
                            />
                        </div>
                    </div>
                </div>
                
                <!-- Section: Address list -->
                <div v-else class="col-span-2 relative py-4 h-fit">
                    <template v-if="addressList.all_addresses.data?.length">
                        <!-- Section: Address list -->
                        <div class="grid gap-x-3 gap-y-4 h-fit transition-all"
                            :class="[isEditAddress ? '' : 'col-span-2 grid-cols-4']">

                            <!-- Section: Address Home -->
                            <div v-if="homeAddress" class="overflow-hidden relative text-xs ring-1 ring-gray-300 rounded-lg h-full transition-all">
                                <div class="flex justify-between border-b border-gray-300 px-3 py-2"
                                    :class="addressList.current_selected_address_id == homeAddress?.id ? 'bg-green-50' : 'bg-gray-100'"
                                >
                                    <div class="flex gap-x-1 items-center relative">
                                        <div class="font-semibold text-sm whitespace-nowrap">
                                            <FontAwesomeIcon icon='fal fa-house' class='' fixed-width aria-hidden='true' />
                                        </div>

                                        <div class="relative">
                                            <Transition name="slide-to-left">
                                                <FontAwesomeIcon v-if="addressList.current_selected_address_id == homeAddress?.id" icon='fas fa-check-circle' class='text-green-500' fixed-width aria-hidden='true' />
                                                <Button
                                                    v-else
                                                    @click="() => onSelectAddress(homeAddress)"
                                                    :label="isSelectAddressLoading == homeAddress?.id ? '' : 'Use this'"
                                                    size="xxs"
                                                    type="tertiary"
                                                    :loading="isSelectAddressLoading == homeAddress?.id"
                                                    v-tooltip="'Apply to this section only'"
                                                />
                                            </Transition>
                                        </div>
                                    </div>

                                    <!-- Action: Pin, edit, delete -->
                                    <div class="flex items-center">
                                        <LoadingIcon v-if="isLoading == 'onPinned' + homeAddress?.id"/>
                                        <FontAwesomeIcon v-else-if="addressList.all_addresses.data?.length > 1" @click="() => onPinnedAddress(homeAddress.id)" icon='fal fa-thumbtack' class='px-0.5 py-1 cursor-pointer' :class="addressList.pinned_address_id === homeAddress?.id ? 'text-green-500' : 'text-gray-400 hover:text-gray-600'" fixed-width aria-hidden='true' v-tooltip="trans('Pin this addres for all scope')" />
                                        <FontAwesomeIcon @click="() => onEditAddress(homeAddress)" icon='fal fa-pencil' class='px-0.5 py-1 text-gray-400 hover:text-gray-600 cursor-pointer' fixed-width aria-hidden='true' v-tooltip="trans('Edit this address')" />
                                    </div>
                                </div>

                                <div v-html="homeAddress?.formatted_address" class="px-3 py-2"></div>
                            </div>

                            <!-- Section: Address looping -->
                        <TransitionGroup>
                                <div v-for="(address, idxAddress) in addressList.all_addresses.data.filter(xxx => xxx.id != addressList.home_address_id)"
                                    :key="idxAddress + address.id"
                                    class="overflow-hidden relative text-xs ring-1 ring-gray-300 rounded-lg h-full transition-all"
                                    :class="[
                                        selectedAddress?.id == address.id ? 'ring-2 ring-offset-4 ring-indigo-500' : ''
                                    ]"
                                >
                                    <div class="flex justify-between border-b border-gray-300 px-3 py-2"
                                        :class="addressList.current_selected_address_id == address.id ? 'bg-green-50' : 'bg-gray-100'"
                                    >
                                        <div class="flex gap-x-1 items-center relative">
                                            <div v-if="address.label" class="font-semibold text-sm whitespace-nowrap">
                                                {{ useTruncate(address.label, 14) }}
                                            </div>
                                            <div v-else class="text-xs italic whitespace-nowrap text-gray-400">
                                                (No label)
                                            </div>
                                            <div class="relative">
                                                <Transition name="slide-to-left">
                                                    <FontAwesomeIcon v-if="addressList.current_selected_address_id == address.id" icon='fas fa-check-circle' class='text-green-500' fixed-width aria-hidden='true' />
                                                    <Button
                                                        v-else
                                                        @click="() => onSelectAddress(address)"
                                                        :label="isSelectAddressLoading == address.id ? '' : 'Use this'"
                                                        size="xxs"
                                                        type="tertiary"
                                                        :loading="isSelectAddressLoading == address.id"
                                                        v-tooltip="'Apply to this section only'"
                                                    />
                                                </Transition>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <LoadingIcon v-if="isLoading === 'onPinned' + address.id"/>
                                            <FontAwesomeIcon v-else-if="addressList.all_addresses.data?.length > 1" @click="() => onPinnedAddress(address.id)" icon='fal fa-thumbtack' class='px-0.5 py-1 cursor-pointer' :class="addressList.pinned_address_id === address.id ? 'text-green-500' : 'text-gray-400 hover:text-gray-600'" fixed-width aria-hidden='true' v-tooltip="trans('Pin this addres for all scope')" />
                                            <FontAwesomeIcon v-if="address.can_edit" @click="() => onEditAddress(address)" icon='fal fa-pencil' class='px-0.5 py-1 text-gray-400 hover:text-gray-600 cursor-pointer' fixed-width aria-hidden='true' v-tooltip="trans('Edit this address')" />
                                            <template v-if="address.can_delete">
                                                <LoadingIcon v-if="isLoading === 'onDelete' + address.id" class="text-sm px-[1px]" />
                                                <FontAwesomeIcon v-else @click="() => onDeleteAddress(address.id)" icon='fal fa-trash-alt' class='px-0.5 py-1 text-gray-400 hover:text-red-500 cursor-pointer' fixed-width aria-hidden='true' v-tooltip="trans('Delete this address')" />
                                            </template>
                                        </div>
                                    </div>
                                    <div v-html="address.formatted_address" class="px-3 py-2"></div>
                                </div>
                            </TransitionGroup>
                        </div>
                    </template>

                    <div v-else class="text-sm flex items-center justify-center h-3/4 font-medium text-center text-gray-400">
                        {{ trans('No address history found') }}
                    </div>
                </div>
            </Transition>
        </div>


        <!-- {{ address.value }} -->
    </div>
</template>

<style scoped></style>