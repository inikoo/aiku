<!--
  -  Author: Jonathan Lopez <raul@inikoo.com>
  -  Created: Wed, 12 Oct 2022 16:50:56 Central European Summer Time, Benalmádena, Malaga,Spain
  -  Copyright (c) 2022, Jonathan Lopez
  -->

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import TableProducts from "@/Components/Tables/Grp/Org/Catalogue/TableProducts.vue"
import { capitalize } from "@/Composables/capitalize"
import { PageHeading as PageHeadingTypes } from "@/types/PageHeading"
import Button from '@/Components/Elements/Buttons/Button.vue'
import { ref } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { trans } from 'laravel-vue-i18n'
import { routeType } from '@/types/route'
import Popover from '@/Components/Popover.vue'
import PureMultiselectInfiniteScroll from '@/Components/Pure/PureMultiselectInfiniteScroll.vue'


const props = defineProps<{
    pageHead: PageHeadingTypes
    title: string
    data: {}
    routes: {
        dataList: routeType
        submitAttach: routeType
    }
}>()


const formProduct = ref({
    selectedId: []
})
const errorMessage = ref<string>('')
const isLoading = ref<string | boolean>(false)
const onSubmitAddService = (closedPopover: Function) => {
    isLoading.value = 'submitAttach'

    router.post(
        route(props.routes.submitAttach.name, props.routes.submitAttach.parameters),
        {
            products: formProduct.value.selectedId
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                closedPopover()
                notify({
                    title: trans('Succes'),
                    text: trans('Successfully attach product.'),
                    type: 'success',
                })
                formProduct.value.selectedId = []
            },
            onError: (errors: any) => {
                // console.log(errors)
                errorMessage.value = errors
                notify({
                    title: trans('Something went wrong.'),
                    text: trans('Failed to attach product, please try again.'),
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
        <template #button-attach-product>
            <Popover>
                <template #button="{ open }">
                    <Button
                        type="secondary"
                        label="Attach product"
                        icon="fal fa-plus"
                        :tooltip="trans('Attach product to this collections')"
                    />
                </template>
                <template #content="{ close: closed }">
                    <div class="w-[350px] px-1 pb-2">
                        <div class="text-sm px-1 my-2 block tabular-nums">{{ trans('Select product') }}: {{ formProduct.selectedId.length }} {{ trans('selected') }} </div>
                        <div class="">
                            <PureMultiselectInfiniteScroll
                                v-model="formProduct.selectedId"
                                :fetchRoute="routes.dataList"
                                mode="multiple"
                            />

                            <p v-if="errorMessage" class="mt-2 text-sm text-red-500">
                                {{ errorMessage }}
                            </p>
                        </div>
                        
                        <div class="flex justify-end mt-3">
                            <Button
                                @click="() => onSubmitAddService(closed)"
                                :style="'save'"
                                :loading="isLoading == 'submitAttach'"
                                :disabled="!formProduct.selectedId.length"
                                label="Save"
                                full
                            />
                        </div>
                        
                        <!-- Loading: fetching service list -->
                        <!-- <div v-if="isLoading === 'fetchProduct'" class="bg-white/50 absolute inset-0 flex place-content-center items-center">
                            <LoadingIcon class="text-5xl" />
                        </div> -->
                    </div>
                </template>
            </Popover>
        </template>
    </PageHeading>
    <TableProducts :data="data" />
</template>

