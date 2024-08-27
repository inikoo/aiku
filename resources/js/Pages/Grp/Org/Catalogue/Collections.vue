<!--
  -  Author: Jonathan Lopez <raul@inikoo.com>
  -  Created: Wed, 12 Oct 2022 16:50:56 Central European Summer Time, Benalmádena, Malaga,Spain
  -  Copyright (c) 2022, Jonathan Lopez
  -->

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import TableCollections from "@/Components/Tables/Grp/Org/Catalogue/TableCollections.vue"
import { capitalize } from "@/Composables/capitalize"
import { PageHeading as PageHeadingTypes } from "@/types/PageHeading"
import Button from '@/Components/Elements/Buttons/Button.vue'
import { ref } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { trans } from 'laravel-vue-i18n'
import axios from 'axios'
import { routeType } from '@/types/route'
import Popover from '@/Components/Popover.vue'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'


const props = defineProps<{
    pageHead: PageHeadingTypes
    title: string
    data: {}
    routes: {
        dataList: routeType
        submitAttach: routeType
    }
}>()

const isLoading = ref<string | boolean>(false)
const errorMessage = ref<string>('')

const formCollection = ref({
    selectedId: []
})


const dataCollectionList = ref([])
const fetchCollectionList = async () => {
    isLoading.value = 'fetchCollection'
    try {
        const xxx = await axios.get(
            route(props.routes.dataList.name, props.routes.dataList.parameters)
        )
        dataCollectionList.value = xxx?.data?.data || []
    } catch (error) {
        // console.log(error)
        notify({
            title: trans('Something went wrong.'),
            text: trans('Failed to fetch collection list'),
            type: 'error',
        })
    }
    isLoading.value = false
}
const onSubmitAddService = (closedPopover: Function) => {
    isLoading.value = 'submitAttach'

    router.post(
        route(props.routes.submitAttach.name, props.routes.submitAttach.parameters),
        {
            id: formCollection.value.selectedId
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                closedPopover()
                notify({
                    title: trans('Succes'),
                    text: trans('Successfully attach collection.'),
                    type: 'success',
                })
                formCollection.value.selectedId = []
            },
            onError: (errors) => {
                // console.log(errors)
                errorMessage.value = errors
                notify({
                    title: trans('Something went wrong.'),
                    text: trans('Failed to attach collection, please try again.'),
                    type: 'error',
                })
            },
            onFinish: () => {
                isLoading.value = false
            }
        }
    )
}
</script>

<template>
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead">
        <template #button-attach-collection>
            <Popover>
                <template #button="{ open }">
                    <Button
                        @click="() => open ? false : fetchCollectionList()"
                        type="secondary"
                        label="Attach collection"
                        icon="fal fa-plus"
                        :tooltip="trans('Attach collection to this collections')"
                    />
                </template>
                <template #content="{ close: closed }">
                    <div class="w-[350px]">
                        <span class="text-xs px-1 my-2">{{ trans('Select collection') }}: </span>
                        <div class="">
                            <PureMultiselect
                                v-model="formCollection.selectedId"
                                autofocus
                                mode="multiple"
                                caret
                                required
                                searchable
                                placeholder="Select Collection"
                                :options="dataCollectionList"
                                label="name"
                                valueProp="id"
                            >
                                <template #label="{ value }">
                                    <div class="w-full text-left pl-4">{{ value.name }} <span class="text-sm text-gray-400">({{ value.code }})</span></div>
                                </template>

                                <template #option="{ option, isSelected, isPointed }">
                                    <div class="">{{ option.name }} <span class="text-sm" :class="isSelected ? 'text-indigo-200' : 'text-gray-400'">({{ option.code }})</span></div>
                                </template>
                            </PureMultiselect>
                            <p v-if="errorMessage" class="mt-2 text-sm text-red-500">
                                {{ errorMessage }}
                            </p>
                        </div>
                        <!-- <div class="mt-3">
                            <span class="text-xs px-1 my-2">{{ trans('Quantity') }}: </span>
                            <PureInput
                                v-model="formAddService.quantity"
                                :placeholder="trans('Quantity')"
                                @keydown.enter="() => onSubmitAddService(action, closed)"
                            />
                            <p v-if="get(formAddService, ['errors', 'quantity'])" class="mt-2 text-sm text-red-600">
                                {{ formAddService.errors.quantity }}
                            </p>
                        </div> -->
                        <div class="flex justify-end mt-3">
                            <Button
                                @click="() => onSubmitAddService(closed)"
                                :style="'save'"
                                :loading="isLoading == 'submitAttach'"
                                :disabled="!formCollection.selectedId.length"
                                label="Save"
                                full
                            />
                        </div>
                        
                        <!-- Loading: fetching service list -->
                        <div v-if="isLoading === 'fetchCollection'" class="bg-white/50 absolute inset-0 flex place-content-center items-center">
                            <LoadingIcon class="text-5xl" />
                        </div>
                    </div>
                </template>
            </Popover>
        </template>
    </PageHeading>
    <TableCollections :data="data" />
</template>