<!--
    TODO: Icon loading is unlimited if change tabs is failed
-->
<script setup lang="ts">
import { inject, ref, watch } from "vue"
import { capitalize } from "@/Composables/capitalize"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faInfoCircle, faPallet, faCircle } from '@fas'
import { faSpinnerThird } from '@fad'
import { faRoad, faClock, faDatabase, faNetworkWired, faEye, faThLarge ,faTachometerAltFast, faMoneyBillWave, faHeart, faShoppingCart, faCameraRetro, faStream} from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { layoutStructure } from "@/Composables/useLayoutStructure"
import type { Navigation } from '@/types/Tabs'
import { routeType } from "@/types/route"
import { Link } from "@inertiajs/vue3"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"

library.add(faInfoCircle, faRoad, faClock, faDatabase, faPallet, faCircle, faNetworkWired, faSpinnerThird, faEye, faThLarge,faTachometerAltFast, faMoneyBillWave, faHeart, faShoppingCart, faCameraRetro, faStream)

const layoutStore = inject('layout', layoutStructure)
const locale = inject('locale', aikuLocaleStructure)

const props = defineProps<{
    tabs_box: {
        label: string
        tabs: {
            label: string
            icon?: string
            indicator?: boolean
            tab_slug: string
            type?: string // 'icon', 'date', 'number', 'currency'
            align?: string
            route?: routeType
            iconClass?: string
            information?: {
                label: string | number
                type?: string // 'icon', 'date', 'number', 'currency'
            }
        }[]
    }[]
    current: string | Number
}>()

const emits = defineEmits<{
    (e: 'update:tab', value: string): void
}>()

const currentTab = ref(props.current)
const tabLoading = ref<boolean | string>(false)

// Method: click Tab
const onChangeTab = async (tabSlug: string) => {
    if(tabSlug === currentTab.value) return  // To avoid click on the current tab occurs loading
    tabLoading.value = tabSlug
    emits('update:tab', tabSlug)
}

// Set new active Tab after parent has changed page
watch(() => props.current, (newVal) => {
    currentTab.value = newVal
    tabLoading.value = false
})


const renderLabelBasedOnType = (data?: {label: string | number, type?: string}, options?: { currency_code?: string}) => {
    if(data?.type === 'number') {
        return locale.number(Number(data?.label))
    } else if (data?.type === 'currency') {
        if (!options?.currency_code) {
            return data?.label
        } else {
            return locale.currencyFormat(options?.currency_code, Number(data?.label))
        }
    } else {
        return data?.label || '-'
    }
    
}
</script>

<template>
    <div>
        <!-- Tabs: Mobile view -->
        <div class="sm:hidden px-3 pt-2">
            <label for="tabs" class="sr-only">Select a tab</label>

            <!-- TODO: use Headless or component Dropdown so the icon is able to show (currrently not) -->
            <!-- <select id="tabs" name="tabs" class="block w-full capitalize rounded-md border-gray-300 focus:border-gray-500 focus:ring-gray-500"
                @input="(val: any) => onChangeTab(val.target.value)"
            >
                <option v-for="(tab, tabSlug) in navigation" :key="tabSlug" :selected="tabSlug == currentTab" :value="tabSlug" class="capitalize">
                    <FontAwesomeIcon v-if="tabLoading == tabSlug" icon="fad fa-spinner-third" class="animate-spin" :class="tabIconClass(tabSlug === currentTab, tab.type, tab.align, tab.iconClass || '')" aria-hidden="true"/>
                    <FontAwesomeIcon v-else-if="tab.icon" :icon="tab.icon" aria-hidden="true"/>
                    {{ tab.title }}
                </option>
            </select> -->
        </div>

        <div class="px-6 flex gap-x-6 my-4 border-b border-gray-300">
            <div v-for="fake in tabs_box" class="relative border border-gray-300 w-full flex flex-col  py-2 transition-all z-10"
                :class="fake.tabs.some(tab => tab.tab_slug === currentTab) ? 'mt-3 rounded-t-xl border-b-0 -mb-0.5 bg-white' : 'bg-gray-500/10 shadow-xl mb-3 rounded-md '"
            >
                <div class="text-center text-gray-500 mb-2">{{ fake.label }}</div>
                <div class="flex gap-x-2 ">
                    <div v-for="tab in fake.tabs" class="w-full flex flex-col items-center"
                        :class="tab.tab_slug === currentTab ? 'text-indigo-600' : ''"
                    >
                        <div @click="onChangeTab(tab.tab_slug)" class="cursor-pointer text-2xl ">
                            {{ renderLabelBasedOnType(tab) }}
                        </div>
                        <div>
                            {{ renderLabelBasedOnType(tab.information)}}
                        </div>
                    </div>
                </div>

            </div>
        </div>
        

        <div class="mt-8"></div>
        
    </div>
</template>
