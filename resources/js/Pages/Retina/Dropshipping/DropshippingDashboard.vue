<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import { ref } from 'vue'

import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { Tabs as TSTabs } from '@/types/Tabs'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { routeType } from '@/types/route'

import { trans } from 'laravel-vue-i18n'
import Modal from '@/Components/Utils/Modal.vue'
import PureInputWithAddOn from '@/Components/Pure/PureInputWithAddOn.vue'
import { notify } from '@kyvg/vue3-notification'


import { faGlobe, faExternalLinkAlt, faUnlink } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faGlobe, faExternalLinkAlt, faUnlink)

const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    tabs: TSTabs
    connectRoute: {
        url: string
    } | null
    createRoute: routeType
    shopify_url: string
    unlinkRoute: routeType

}>()

const isModalOpen = ref<string | boolean>(false)
const websiteInput = ref<string | null>(null)

const isLoading = ref<string | boolean>(false)
const onCreateStore = () => {
    router[props.createRoute.method || 'post'](
        route(props.createRoute.name, props.createRoute.parameters),
        {
            name: websiteInput.value
        },
        {
            onStart: () => isLoading.value = true,
            onError: (error) => {
                notify({
                    title: trans('Something went wrong'),
                    text: error,
                    type: 'error',
                })
            },
            onSuccess: () => {
                isModalOpen.value = false
            },
            onFinish: () => isLoading.value = false
        }
    )
}

</script>


<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <!-- <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" /> -->

    <!-- <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab" /> -->
    <div class="px-6">
        <div class="text-xl py-2 w-fit">E-Commerce</div>
        <div class="flex gap-4">
            <div class="bg-gray-50 border border-gray-200 rounded-md w-72 p-4">
                <div class="mb-4 border-b border-gray-300 pb-4 flex gap-x-4 items-center text-xl">
                    <img src="https://cdn-icons-png.flaticon.com/256/5968/5968919.png" alt="" class="h-12">
                    <div class="flex flex-col">
                        <div class="font-semibold">Shopify</div>
                        <div class="text-xs text-gray-500">({{ trans("Manage product") }})</div>
                    </div>
                </div>

                <!-- Button: Connect -->
                <div class="relative w-full">
                    <Transition name="spin-to-down">
                        <div v-if="connectRoute?.url" class="w-full flex justify-end gap-x-2">
                            <Link as="button" :href="route(unlinkRoute.name, unlinkRoute.parameters)"
                                :method="unlinkRoute.method" @start="isLoading = 'unlink'" @error="(error) => notify({
                                    title: trans('Something went wrong.'),
                                    text: trans('Please try again'),
                                    type: 'error',
                                }) " @finish="isLoading = false">
                            <Button :loading="isLoading === 'unlink'" label="Unlink" type="negative"
                                icon="fal fa-unlink" size="xs" />
                            </Link>
                            <a target="_blank" :href="connectRoute?.url" class="w-full">
                                <Button label="Open" key="secondary" full iconRight="fal fa-external-link-alt"
                                    size="xs" />
                            </a>
                        </div>

                        <!-- Button: Create -->
                        <div v-else class="w-full flex justify-end">
                            <Button @click="() => isModalOpen = 'shopify'" label="Create" type="primary" full />
                        </div>
                    </Transition>
                </div>
            </div>

            <div
                class="border border-dashed  border-gray-300 rounded-md w-72 p-4 flex items-center justify-center text-gray-400 italic">
                {{ trans('Coming soon') }}
            </div>
        </div>
    </div>

    <Modal :isOpen="!!isModalOpen" @onClose="isModalOpen = false" width="w-[500px]">
        <div class="h-40">
            <div class="mb-4">
                <div class="text-center font-semibold text-xl">
                    {{ trans('Select your store name') }}
                </div>

                <div class="text-center text-xs text-gray-500">
                    {{ trans('This is the url that your store can be accessed') }}
                </div>
            </div>

            <PureInputWithAddOn v-model="websiteInput" :leftAddOn="{
                    icon: 'fal fa-globe'
                }" :rightAddOn="{
                    label: shopify_url
                }" @keydown.enter="() => onCreateStore()" />

            <Button @click="() => onCreateStore()" full label="Create" :loading="!!isLoading" class="mt-6" />
        </div>
    </Modal>
</template>