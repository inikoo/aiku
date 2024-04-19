<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 23 Feb 2024 09:56:34 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup  lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"
import { faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faCross } from "@fal"
import { Link } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Table from "@/Components/Table/Table.vue"
import Modal from "@/Components/Utils/Modal.vue"
import { ref } from 'vue'

import { PalletDelivery } from "@/types/pallet-delivery"
import TagPallete from '@/Components/TagPallete.vue'

library.add(faPlus, faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faCross)

const props = defineProps<{
    data: {}
    title: string
    pageHead: {}
}>()

console.log('props',props)

const openModal = ref(false)
const loading = ref(false)

function palletDeliveryRoute(palletDelivery: PalletDelivery) {
    switch (route().current()) {
        default:
            return route(
                'retina.storage.pallet-deliveries.show',
                [
                    palletDelivery.slug
                ])

    }
}

const handleClick = (action: Action) => {
    if (action.disabled) openModal.value = true
    else {
        const href = action.route?.name ? route(action.route?.name, action.route?.parameters) : action.href?.name ? route(action.href?.name, action.href?.parameters) : '#'
        const method = action.route?.method ?? 'get'
        router[method](
            href,
            {
                onBefore: () => { loading.value = true },
                onerror: () => { loading.value = false }
            })
    }
};

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-new-delivery="{ action : linkButton }">
           <Button :style="linkButton.action.style" :icon="linkButton.action.icon" :label="linkButton.action.label" size="l"
                :loading="loading" @click="() => handleClick(linkButton.action)" /> 
        </template>
    </PageHeading>

    <Table :resource="data" class="mt-5">
        <template #cell(reference)="{ item: palletDelivery }">
            <Link :href="palletDeliveryRoute(palletDelivery)" class="specialUnderline">
                {{ palletDelivery['reference'] }}
            </Link>
        </template>

        <template #cell(state)="{ item: palletDelivery }">
            <TagPallete :stateIcon="palletDelivery.state_icon" />
        </template>


        <template #buttondeliveries="{ linkButton: linkButton }">
            <Link v-if="linkButton?.route?.name" method="post"
                :href="route(linkButton?.route?.name, linkButton?.route?.parameters)"
                class="ring-1 ring-gray-300 overflow-hidden first:rounded-l last:rounded-r">
                <Button :style="linkButton.style" :label="linkButton.label"
                    class="h-full capitalize inline-flex items-center rounded-none text-sm border-none font-medium shadow-sm focus:ring-transparent focus:ring-offset-transparent focus:ring-0">
                </Button>
            </Link>
        </template>
    </Table>
    <Modal :isOpen="openModal" @onClose="openModal = false" width="w-2/4">
        <main class="grid min-h-full place-items-center bg-white px-6 py-24 sm:py-32 lg:px-8">
            <div class="text-center">
                <p class="text-base font-semibold text-indigo-600">403 - Access Denied</p>
                <h1 class="mt-4 text-xl font-bold tracking-tight text-gray-900 sm:text-xl">You do not have permission
                    to
                    access this page</h1>
                <p class="mt-6 text-sm leading-7 text-gray-600">Sorry, the page you are looking for could not be
                    found.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">

                    <div @click="() => openModal = false" class="text-sm font-semibold text-gray-900">Close </div>

                    <div
                        class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Contact Suport<span aria-hidden="true">&rarr;</span>
                    </div>
                </div>
            </div>
        </main>
    </Modal>
</template>
