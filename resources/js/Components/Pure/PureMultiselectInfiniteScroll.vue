<script setup lang='ts'>
import Multiselect from "@vueform/multiselect"
import { debounce } from 'lodash'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'

import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
import { Links, Meta, Table } from '@/types/Table'
import { onMounted, onUnmounted, ref } from "vue"
import { notify } from "@kyvg/vue3-notification"
import { trans } from "laravel-vue-i18n"
import axios from "axios"
import { router } from '@inertiajs/vue3'
import { routeType } from "@/types/route"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronLeft, faChevronRight } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faChevronLeft, faChevronRight)

const model = defineModel()
const props = defineProps<{
    mode?: "single" | "multiple" | "tags"
    classes?: {}
    fetchRoute: routeType
    required?: boolean
    placeholder?: string
}>()



const isLoading = ref<string | boolean>(false)

const getUrlFetch = (additionalParams: {}) => {
    return route(
        props.fetchRoute.name,
        {
            ...props.fetchRoute.parameters,
            ...additionalParams
        }
    )
}

const optionsList = ref<any[]>([])
const optionsMeta = ref<Meta | null>(null)
const optionsLinks = ref<Links | null>(null)
const fetchProductList = async (url?: string) => {
    const fetchUrl = url || route(props.fetchRoute.name, props.fetchRoute.parameters)

    isLoading.value = 'fetchProduct'
    try {
        const xxx = await axios.get(
            fetchUrl
        )
        optionsList.value = optionsList.value.concat(xxx?.data?.data)
        optionsMeta.value = xxx?.data.meta || null
        optionsLinks.value = xxx?.data.links || null
    } catch (error) {
        // console.log(error)
        notify({
            title: trans('Something went wrong.'),
            text: trans('Failed to fetch product list'),
            type: 'error',
        })
    }
    isLoading.value = false
}
    
const onSearchQuery = debounce((query: string) => {
    fetchProductList(getUrlFetch({'filter[global]': query}))
}, 500)


// Method: fetching next page
const onFetchNext = () => {
    const dropdown = document.querySelector('.multiselect-dropdown')
    // console.log(dropdown?.scrollTop, dropdown?.clientHeight, dropdown?.scrollHeight)

    const bottomReached = (dropdown?.scrollTop || 0) + (dropdown?.clientHeight || 0) >= (dropdown?.scrollHeight || 10) - 10
    if (bottomReached && optionsLinks.value?.next && isLoading.value != 'fetchProduct') {
        // console.log(dropdown?.scrollTop, dropdown?.clientHeight, dropdown?.scrollHeight)
        fetchProductList(optionsLinks.value.next)
    }
}

onMounted(() => {
    const dropdown = document.querySelector('.multiselect-dropdown')
    // console.log('bb', dropdown, dropdown?.scrollTop)
    if (dropdown) {
        dropdown.addEventListener('scroll', onFetchNext)
    }
})

onUnmounted(() => {
    const dropdown = document.querySelector('.multiselect-dropdown')
    if (dropdown) {
        dropdown.removeEventListener('scroll', onFetchNext)
    }
})

</script>

<template>
    <!-- <pre>{{ options }}</pre> -->
    <!-- <div class="relative w-full text-gray-600 rounded-sm"> -->
        <Multiselect
            v-model="model"
            :classes="{
                placeholder: 'pointer-events-none absolute top-1/2 z-10 -translate-y-1/2 select-none text-sm text-left w-full pl-4 font-light text-gray-400 opacity-1',
                ...classes,
            }"
            :canClear="!required"
            :mode="mode || 'single'"
            :closeOnSelect="mode == 'multiple' ? false : true"
            :canDeselect="!required"
            :hideSelected="false"
            :caret="isLoading ? false : true"
            :clearOnSelect="false"
            searchable
            :clearOnBlur="false"
            clearOnSearch
            autofocus
            :loading="isLoading === 'fetchProduct'"
            :placeholder="placeholder || trans('Select option')"
            :options="optionsList"
            label="name"
            :resolve-on-load="true"
            :min-chars="1"
            valueProp="id"
            @open="() => optionsList?.length ? false : fetchProductList()"
            @search-change="(ee) => onSearchQuery(ee)"
        >

            <template #singlelabel="{ value }">
                <slot name="singlelabel" :value>
                    <div class="w-full text-left pl-4">{{ value.name }} <span class="text-sm text-gray-400">({{ value.code }})</span></div>
                </slot>
            </template>

            <template #option="{ option, isSelected, isPointed }">
                <slot name="option" :option :isSelected :isPointed>
                    <div class="">{{ option.name }} <span class="text-sm" :class="isSelected(option) ? 'text-indigo-200' : 'text-gray-400'">({{ option.code }})</span></div>
                </slot>
            </template>

            <template #spinner>
                <LoadingIcon class="mr-3" />
            </template>

            <template #afterlist>
                <div v-if="isLoading === 'fetchProduct'" class="py-2 flex justify-center text-xl">
                    <LoadingIcon />
                </div>
                <!-- <div v-if="optionsMeta?.current_page && optionsList?.length && (optionsLinks?.prev || optionsLinks?.next)" class="flex justify-center border-t border-gray-300 gap-x-2 py-2 px-4 cursor-default">
                    <div 
                        @click="() => optionsLinks?.prev ? fetchProductList(optionsLinks?.prev) : false"
                        class="flex justify-center items-center py-1 px-2 text-gray-500 hover:text-gray-700 border border-transparent hover:border-gray-300 rounded-md"
                        :class="optionsLinks?.prev ? 'cursor-pointer ' : 'opacity-0'"
                    >
                        <FontAwesomeIcon icon='fal fa-chevron-left' class='' fixed-width aria-hidden='true' />
                    </div>

                    <div v-if="optionsMeta.current_page" class="w-16">
                        <PureInputNumber
                            v-model="optionsMeta.current_page"
                            @update:modelValue="(value) => fetchProductList(getUrlFetch({page: value}))"
                        />
                    </div>

                    <div 
                        @click="() => optionsLinks?.next ? fetchProductList(optionsLinks?.next) : false"
                        class="flex justify-center items-center py-1 px-2 text-gray-500 hover:text-gray-700 border border-transparent hover:border-gray-300 rounded-md"
                        :class="optionsLinks?.next ? 'cursor-pointer ' : 'opacity-0'"
                    >
                        <FontAwesomeIcon icon='fal fa-chevron-right' class='' fixed-width aria-hidden='true' />
                    </div>
                </div> -->
            </template>
        </Multiselect>
    <!-- </div> -->
</template>

<style src="@vueform/multiselect/themes/default.css"></style>

<style>
.multiselect-single-label {
    padding-right: calc(1.5rem + var(--ms-px, .035rem)*3) !important;
}

.multiselect-search {
    background: transparent !important;
}

/* For Multiselect */
.multiselect-option.is-selected,
.multiselect-option.is-selected.is-pointed {
	@apply bg-gray-500 text-white;
}

.multiselect-option.is-selected.is-disabled {
	@apply bg-gray-200 text-white;
}

.multiselect.is-active {
	border: var(--ms-border-width-active, var(--ms-border-width, 1px)) solid
		var(--ms-border-color-active, var(--ms-border-color, #787878)) !important;
	box-shadow: 0 0 0 var(--ms-ring-width, 3px) var(--ms-ring-color, rgba(42, 42, 42, 0.188)) !important;
	/* box-shadow: 4px 0 0 0 calc(4px + 4px) rgba(42, 42, 42, 1); */
}

.multiselect-dropdown {
    max-height: 250px !important;
}
.multiselect-tags-search {
    @apply focus:outline-none focus:ring-0
}

.multiselect-tags {
    @apply m-0.5
}

.multiselect-tag-remove-icon {
    @apply text-lime-800
}
</style>