<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 15 Sept 2022 16:07:20 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, defineAsyncComponent, ref } from "vue"
import type { Component } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import TablePurchaseOrderTransactions from "@/Components/Tables/Grp/Org/Procurement/TablePurchaseOrderTransactions.vue"
import { capitalize } from "@/Composables/capitalize"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import { PageHeading as PageHeadingTS } from '@/types/PageHeading'
import { BoxNote as BoxNoteTS } from '@/types/Components/BoxNotes'
import { routeType } from '@/types/route'
import { trans } from 'laravel-vue-i18n'
import { notify } from '@kyvg/vue3-notification'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import PureTextarea from '@/Components/Pure/PureTextarea.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import AlertMessage from '@/Components/Utils/AlertMessage.vue'
import BoxNote from '@/Components/Pallet/BoxNote.vue'
import Popover from '@/Components/Popover.vue'
import TableAttachments from "@/Components/Tables/Grp/Helpers/TableAttachments.vue"


import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faStickyNote } from '@fal'
library.add( faStickyNote )

const props = defineProps<{
    title: string,
    pageHead: PageHeadingTS
    tabs: {
        current: string
        navigation: {}
    },
    showcase: {}
    items: {}
    history: {}

    
    alert?: {
        status: string
        title?: string
        description?: string
    }
    notes: {
        note_list: BoxNoteTS[]
    }
    routes: {
        updateOrderRoute: routeType
        products_list: routeType
    }
    attachments?: {}
}>()


const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const component = computed(() => {
    const components: Component = {
        history: TableHistories,
        items: TablePurchaseOrderTransactions
        attachments: TableAttachments,
    }

    return components[currentTab.value]
})


const isLoadingButton = ref<string | boolean>(false)


// Section: add notes (on popup pageheading)
const errorNote = ref('')
const noteToSubmit = ref({
    selectedNote: '',
    value: ''
})
const onSubmitNote = async (closePopup: Function) => {
    
    try {
        router.patch(route(props.routes.updateOrderRoute.name, props.routes.updateOrderRoute.parameters), {
            [noteToSubmit.value.selectedNote]: noteToSubmit.value.value
        },
        {
            headers: { "Content-Type": 'application/json' },
            onStart: () => isLoadingButton.value = 'submitNote',
            onError: (error: any) => errorNote.value = error,
            onFinish: () => isLoadingButton.value = false,
            onSuccess: () => {
                closePopup(),
                noteToSubmit.value.selectedNote = ''
                noteToSubmit.value.value = ''
            },
        })
    } catch (error) {
        notify({
			title: trans("Something went wrong"),
			text: trans("Failed to update the note, try again."),
			type: "error",
		})
    }
}
</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #otherBefore>
            <!-- Section: Add notes -->
            <Popover v-if="!notes?.note_list?.some(item => !!(item?.note?.trim()))">
                <template #button="{ open }">
                    <Button
                        icon="fal fa-sticky-note"
                        type="tertiary"
                        label="Add notes"
                    />
                </template>
                <template #content="{ close: closed }">
                    <div class="w-[350px]">
                        <span class="text-xs px-1 my-2">{{ trans('Select type note') }}: </span>
                        <div class="">
                            <PureMultiselect
                                v-model="noteToSubmit.selectedNote"
                                @update:modelValue="() => errorNote = ''"
                                :placeholder="trans('Select type note')"
                                required
                                :options="[{label: 'Public note', value: 'public_notes'}, {label: 'Private note', value: 'internal_notes'}]"
                                valueProp="value"
                            />

                            <!-- <p v-if="get(formAddService, ['errors', 'service_id'])" class="mt-2 text-sm text-red-500">
                                {{ formAddService.errors.service_id }}
                            </p> -->
                        </div>

                        <div class="mt-3">
                            <span class="text-xs px-1 my-2">{{ trans('Note') }}: </span>
                            <PureTextarea
                                v-model="noteToSubmit.value"
                                :placeholder="trans('Note')"
                                @keydown.enter="() => onSubmitNote(closed)"
                            />
                        </div>

                        <p v-if="errorNote" class="mt-2 text-sm text-red-600">
                            *{{ errorNote }}
                        </p>

                        <div class="flex justify-end mt-3">
                            <Button
                                @click="() => onSubmitNote(closed)"
                                :style="'save'"
                                :loading="isLoadingButton === 'submitNote'"
                                :disabled="!noteToSubmit.value"
                                label="Save"
                                full
                            />
                        </div>
                        
                        <!-- Loading: fetching service list -->
                        <div v-if="isLoadingButton === 'submitNote'" class="bg-white/50 absolute inset-0 flex place-content-center items-center">
                            <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin text-5xl' fixed-width aria-hidden='true' />
                        </div>
                    </div>
                </template>
            </Popover>
        </template>
    </PageHeading>

    
    <!-- Section: Pallet Warning -->
    <div v-if="alert?.status" class="p-2 pb-0">
        <AlertMessage :alert />
    </div>
    
    <!-- Section: Box Note -->
    <div class="relative">
        <Transition name="headlessui">
            <div v-if="notes?.note_list?.some(item => !!(item?.note?.trim()))" class="p-2 grid sm:grid-cols-3 gap-y-2 gap-x-2 h-fit lg:max-h-64 w-full lg:justify-center border-b border-gray-300">
                <BoxNote
                    v-for="(note, index) in notes.note_list"
                    :key="index+note.label"
                    :noteData="note"
                    :updateRoute="routes.updateOrderRoute"
                />
            </div>
        </Transition>
    </div>

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab" />
</template>
