<script setup lang='ts'>
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import InputIcon from 'primevue/inputicon'
import InputText from 'primevue/inputtext'
import SelectButton from 'primevue/selectbutton'
import DataView from 'primevue/dataview'
import IconField from 'primevue/iconfield'
import Rating from 'primevue/rating'
import { FilterMatchMode } from '@primevue/core/api'
import { onMounted, ref } from 'vue'
import { useLocaleStore } from '@/Stores/locale'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { routeType } from '@/types/route'
import axios from 'axios'
import { Link, router } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch, faThLarge, faListUl, faStar as falStar } from '@fal'
import { faStar } from '@fas'
import Select from 'primevue/select'
import { library } from '@fortawesome/fontawesome-svg-core'
import Image from '@/Components/Image.vue'
import { notify } from '@kyvg/vue3-notification'
import Modal from '@/Components/Utils/Modal.vue'
library.add(faSearch, faThLarge, faListUl, faStar, falStar)

declare global {
    interface Window {
        sessionToken: string; // or the correct type if it's not a string
    }
}

const props = defineProps<{
    user: {}
    shop: string
    shopUrl: string
    showIntro: boolean
    routes: {
        products: routeType
        store_product: routeType
        get_started: routeType
    }
    // token: string
    // token_request: string
}>()

console.log('token', props)
// const xxToken = Object.keys(props.token)[1].match(/login_pupil_([a-f0-9]+)/)?.[1]
const locale = useLocaleStore()

const isModalGetStarted = ref(props.showIntro)

const filters = ref({
    'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
})

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'INSTOCK':
            return 'success';

        case 'LOWSTOCK':
            return 'warn';

        case 'OUTOFSTOCK':
            return 'danger';

        default:
            return null;
    }
}

// Fetch: product
const realProducts = ref([])

// Selected product
const isLoadingSubmit = ref(false)
const selectedProducts = ref([])
const isSelected = (id: number) => {
    return selectedProducts.value.some(item => item.id === id);
}

const onClickGetStarted = () => {
    isModalGetStarted.value = false

    router[props.routes.get_started.method || 'post'](route(props.routes.get_started.name, props.routes.get_started.parameters), {

    }, {
        headers: {
            Authorization: `Bearer ${window.sessionToken}`
        },
        preserveState: true,
        onError: () => {
            console.error('error get started: ', error)
        }
    })
}
const openWebsite = () => {
    window.open(props.shopUrl, '_blank');
};
</script>

<template>
    <div v-if="props.showIntro" class="relative isolate overflow-hidden px-6 py-8 text-center sm:rounded-3xl sm:px-12">
        <h2 class="mx-auto max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl">
            {{ trans(`Let's get started.`) }}
        </h2>
        <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-gray-500">
            It's looks like this is the first time you integrate Shopify, let's have a look what you can do.
        </p>
        <div class="mt-10 flex items-center justify-center gap-x-6">
            <Button @click="() => onClickGetStarted()" type="black" size="l" label="Configure" />
        </div>
    </div>
    <div v-else class="relative isolate overflow-hidden px-6 py-8 text-center sm:rounded-3xl sm:px-12">
        <h2 class="mx-auto max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl">
            {{ trans(`Welcome to ${props.shop}!`) }}
        </h2>
        <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-gray-500">
            You can visit your account in our fulfilment website to see more.
        </p>
        <div class="mt-10 flex items-center justify-center gap-x-6">
            <Button @click="openWebsite" type="black" size="l" label="Visit Fulfilment Website" />
        </div>
    </div>
</template>
