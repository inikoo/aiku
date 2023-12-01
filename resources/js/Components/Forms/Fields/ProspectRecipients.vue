<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 24 Nov 2023 17:31:58 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang='ts'>
import { ref, watch, onMounted } from 'vue'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import ProspectQueries from '@/Components/Forms/Fields/ProspectQueries.vue'
import ProspectQueryBuilder from '@/Components/Forms/Fields/ProspectQuery/ProspectQueryBuilder.vue'
import ProspectSelect from '@/Components/Forms/Fields/ProspectsSelect.vue'
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"
import { trans } from "laravel-vue-i18n";
import {useLocaleStore} from "@/Stores/locale";

const props = defineProps<{
    form: {
        [key: string]: {
            recipient_builder_type: string  // query | custom | prospects
            recipient_builder_data: {
                query: number
                custom: {}
                prospects: {}
            }
        }
    }
    fieldName: string  // "query"
    fieldData: {

    }
    options: {
        query: {}
        custom: {}
        prospects: {}
        [key: string]: {}
    }
}>()

const selectedIndex = ref(0)
const recipientsCount = ref(0)

const categories = [
    {
        name: 'query',
        fieldName: props.fieldName,
        label: trans('Query'),
        component: ProspectQueries,
        options : props.options["query"]
    },
    {
        name: 'custom_prospects_query',
        fieldName: [props.fieldName, 'recipient_builder_data', 'custom_prospects_query'],
        label: trans('Custom'),
        component: ProspectQueryBuilder,
        options : {
            use : ["tags", "prospect_last_contacted"],
            ...props.options["custom_prospects_query"]

        }
    },
    {
        name: 'prospects',
        fieldName: [ props.fieldName, 'recipient_builder_data', 'prospects'],
        label: trans('Prospects'),
        component: ProspectSelect,
        options : props.options["prospects"]
    },
].filter((item) => {
    // Filter out 'query' category if props.options.data.length is 0
    if (props.options.query.data.length === 0 && item.name === 'query') {
        return false; // Exclude 'query' category
    }
    return true; // Include other categories
});
const locale = useLocaleStore();


const getEstimateRecipients = async () => {
        try {
            const formData = props.form.data();
            const response = await axios.get(
                route('org.crm.shop.prospects.mailshots.estimated-recipients', route().params),
                {
                    params: {
                        ...formData
                    }
                }
            );
            //if(response.data.type==='query'){
            //    props.form[props.fieldName].recipient_builder_data.query = response.data.count //todo

            recipientsCount.value = response.data ;

        } catch (error) {
            console.error(error); // Log the error for debugging purposes

            // Notify user about the failure
            notify({
                title: "Failed",
                text: "Failed to count recipients",
                type: "error"
            });
        }
}

const changeTab=(tabIndex : number)=>{
    props.form[props.fieldName].recipient_builder_type = categories[tabIndex].name
    selectedIndex.value = tabIndex
}

watch(props.form[props.fieldName],getEstimateRecipients, {deep: true})
watch(props.options,getEstimateRecipients, {deep: true})

const getParams = () => {
    const pathname = location.search
    const urlParams = new URLSearchParams(pathname);
    const paramsObject = {};
    for (const [key, value] of urlParams) {
        paramsObject[key] = value;
    }
    return paramsObject
}

onMounted(() => {
    const pathname = getParams()
    if (pathname.tags) {
        props.form[props.fieldName].recipient_builder_type = "custom"
        selectedIndex.value = 1
    }else{
     const index = categories.findIndex((item)=>item.name ==  props.form[props.fieldName].recipient_builder_type)
     if(index != -1)  selectedIndex.value = index
    }
})


</script>

<template>
    <div class="w-full px-2 sm:px-0">
        <TabGroup manual @change="changeTab"  :selectedIndex="selectedIndex">
            <TabList class="flex space-x-8 ">
                <Tab v-for="(category, categoryIndex) in categories" as="template" :key="categoryIndex"
                    v-slot="{ selected }">
                    <button :class="[
                        'whitespace-nowrap border-b-2 py-1.5 px-1 text-sm font-medium focus:ring-0 focus:outline-none',
                        selected
                            ? 'border-org-5s00 text-org-500'
                            : 'border-transparent text-gray-400 hover:border-gray-300',
                    ]">
                        {{ category.label }}
                    </button>
                </Tab>
                <div style="margin-left: auto;">
                    <button  v-if="recipientsCount.type == 'prospects'" class="whitespace-nowrap border-b-2 py-1.5 px-1 text-sm focus:ring-0 focus:outline-none border-transparent text-org-500  font-semibold">
                      {{trans('Total recipients')}}:  {{ recipientsCount.count }}
                    </button>
                    <button  v-if="recipientsCount.type == 'custom_prospects_query'" class="whitespace-nowrap border-b-2 py-1.5 px-1 text-sm focus:ring-0 focus:outline-none border-transparent text-org-500  font-semibold">
                        {{trans('Total recipients')}}:   {{ recipientsCount.count }}
                    </button>

                    <button  v-if="recipientsCount.type == 'query'" class="whitespace-nowrap border-b-2 py-1.5 px-1 text-sm focus:ring-0 focus:outline-none border-transparent text-org-500  font-semibold">
                        {{trans('Total recipients')}}:  {{ recipientsCount.count }}
                    </button>
                </div>
            </TabList>

            <TabPanels class="mt-2">
                <TabPanel v-for="(category, categoryIndex) in categories" :key="categoryIndex"
                    class="rounded bg-gray-50 p-3 ring-2 ring-gray-200 focus:outline-none">
                    <component :is="category.component"
                        :form="form"
                        :fieldName="category.fieldName"
                        :tabName="category.name"
                        :fieldData="fieldData"
                        :options="category.options"
                    />
                </TabPanel>
            </TabPanels>
        </TabGroup>
    </div>
    <!-- {{ options }} -->
    <!-- <pre>{{ form[fieldName] }}</pre> -->
 <!--    <pre>{{ form }}</pre> -->
</template>
