<script setup lang="ts">
import { faInfoCircle } from '@far/'
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref, watch, reactive, onMounted } from 'vue'
import descriptor from './descriptor'
import { get, set, isArray } from 'lodash'
import { faExclamationCircle, faCheckCircle, faChevronDown, faChevronRight } from '@fas/';
import { faBoxOpen } from '@fal/';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { trans } from "laravel-vue-i18n";
import Tags from './FieldQueryBuilder/Tags.vue'
import PropspectBy from './FieldQueryBuilder/ProspectWith.vue'
import LastContact from './FieldQueryBuilder/ProspectLastContacted.vue'

library.add(faChevronDown, faInfoCircle, faExclamationCircle, faCheckCircle, faChevronRight, faBoxOpen)

const props = withDefaults(defineProps<{
    form?: any
    fieldName: string
    tabName: string
    options: string[] | object
    fieldData?: {
        placeholder?: string
        required?: boolean
        mode?: string
        searchable?: boolean
    }
}>(), {
    options: {
        use: ['prospect_can_contact_by', "tags", "prospect_last_contacted"],
    }

})


const emits = defineEmits();
const sectionValue = ref([])
const schemaForm = descriptor['schemaForm'].filter((item) => props.options.use.includes(item.name))

const getComponent = (componentName: string) => {
    const components: any = {
        "prospect_can_contact_by": PropspectBy,
        "tags" : Tags,
        "prospect_last_contacted" : LastContact


    };
    return components[componentName] ?? null;

};


const setFormValue = (data, fieldName) => {
    if (isArray(fieldName)) {  /* if fieldName array */
        if (get(data, fieldName)) return get(data, fieldName, {});  /* Check if data null or undefined or has an object*/
        else return {}
    } else return get(data, fieldName, {}); /* if fieldName string */

};

const value = reactive(setFormValue(props.form, props.fieldName));/* get value from form */


/* set form when data changes */
const updateFormValue = (newValue) => {
    let target = props.form;
    set(target, props.fieldName, newValue);
    emits("update:form", target);
};

const changeSection = (index: Number) => {
    if (sectionValue.value.includes(index)) set(value, schemaForm[index].name, schemaForm[index].value);
    else delete value[schemaForm[index].name]
}


watch(value, (newValue) => {
    updateFormValue(newValue);
});

onMounted(()=>{
    for(const item in value){
        const index =  schemaForm.findIndex((i)=> i.name == item)
        if(index != -1) sectionValue.value.push(index)
    }
})


</script>

<template>
    <div class="flex">
        <div class="w-[20%] px-2">
            <div v-for="(sectionData, sectionIdx ) in schemaForm" :key="sectionIdx" class="relative py-1">
                <div
                    class="flex w-full justify-between rounded-lg bg-purple-100 px-4 py-2 text-left text-sm font-medium text-purple-900 hover:bg-purple-200 focus:outline-none focus-visible:ring focus-visible:ring-purple-500/75">
                    <label :for="sectionData.name" class="ml-2">{{ sectionData.label }}</label>
                    <input type="checkbox" :id="sectionData.name" :key="sectionData.name" :value="sectionIdx"
                        v-model="sectionValue" @change="changeSection(sectionIdx)"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                </div>
            </div>
        </div>

        <div class="w-[80%] bg-gray-50 p-4 rounded-md border border-gray-300">

            <!--  if value is null -->
            <div v-if="Object.keys(value).length === 0 && value.constructor === Object"
                class="flex justify-center items-center">
                <div class="text-center">
                    <font-awesome-icon class="h-16" :icon="['fal', 'box-open']" />
                    <div class="mt-1 text-xs">You don't have any filter data</div>
                </div>
            </div>
            <!-- end  if value is null -->



            <div v-for="[key, item] in Object.entries(value)" :key="key">
                <Disclosure v-if="schemaForm.find(section => section.name === key)" as="div" class="mt-2" v-slot="{ open }" :defaultOpen="true">
                    <DisclosureButton
                        class="flex w-full justify-between  bg-purple-100 px-4 py-2 text-left text-sm font-medium text-purple-900 hover:bg-purple-200 focus:outline-none focus-visible:ring focus-visible:ring-purple-500/75">
                        <div>
                            <font-awesome-icon class="h-[10px] pr-2 py-[2px]"
                                :icon="open ? ['fas', 'chevron-down'] : ['fas', 'chevron-right']" />
                            <span>{{ schemaForm.find(section => section.name === key).label }}</span>
                        </div>
                        <div class="flex gap-2">
                            <VTooltip>
                                <font-awesome-icon :icon="['far', 'info-circle']" />
                                <template #popper>
                                    {{ schemaForm.find(section => section.name === key).information }}
                                </template>
                            </VTooltip>
                        </div>
                    </DisclosureButton>
                    <DisclosurePanel class="px-4 pt-3 pb-2 text-sm text-gray-500 bg-white border-gray-300 border border-t-0">

                            <component :is="getComponent(key)" :value="value" :fieldName="key">
                            </component>

                    </DisclosurePanel>
                </Disclosure>
            </div>



        </div>

    </div>
</template>

<style lang="scss">
.multiselect-tags-search {
    @apply focus:outline-none focus:ring-0
}

.multiselect.is-active {
    @apply shadow-none
}

.multiselect-tags {
    @apply m-0.5
}

.multiselect-tag-remove-icon {
    @apply text-lime-800
}
</style>
