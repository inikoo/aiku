<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 23:44:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import PickList from 'primevue/picklist';
import { ref, onMounted } from 'vue';
import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';
import Multiselect from "@vueform/multiselect"
import { trans } from 'laravel-vue-i18n';
import Tag from '@/Components/Tag.vue';
import PureInput from '@/Components/Pure/PureInput.vue';
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue';

import { faExclamationCircle, faCheckCircle } from '@fas'
import { faCopy, faUser } from '@fal'
import { faSpinnerThird } from '@fad'
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fromPairs } from 'lodash';

library.add(faExclamationCircle, faCheckCircle, faSpinnerThird, faCopy)

const props = defineProps<{
    form: any
    fieldName: string
    options?: any
    fieldData?: {
        type: string
        placeholder: string
        readonly?: boolean
        copyButton: boolean
        maxLength?: number
    }
}>()
console.log('form', props)
const products = ref(null);
const contact = [
    { label: trans("Never"), value: false },
    { label: trans("Last Contact"), value: true },
]
const tags = [
    {
        id: 1,
        slug: "aw",
        name: "aw"
    },
    {
        id: 3,
        slug: "advantage",
        name: "Advantage"
    },
    {
        id: 4,
        slug: "staff",
        name: "Staff"
    },
    {
        id: 5,
        slug: "dev",
        name: "Dev"
    },
    {
        id: 6,
        slug: "mailchimp",
        name: "mailchimp"
    },
    {
        id: 7,
        slug: "slovak",
        name: "slovak"
    },
    {
        id: 8,
        slug: "awukds",
        name: "AWUKDS"
    },
    {
        id: 9,
        slug: "spamtest",
        name: "SPAMTEST"
    },
    {
        id: 10,
        slug: "tomas",
        name: "tomas"
    },
    {
        id: 11,
        slug: "awad",
        name: "AWAD"
    }
]

const logic = [
    { label: trans("All"), value: "all" },
    { label: trans("Any"), value: "any" },
]
</script>
<template>
    <div v-if="form.recipient_type == 'query'" class="grid grid-cols-1 md:grid-cols-8 gap-2">
        <div class="md:col-span-8 grid sm:grid-cols-1 md:grid-cols-4 gap-2 h-auto mb-3">
            <div :class="'from-blue-500  to-sky-300'"
                class="bg-gradient-to-tr text-white flex flex-col justify-between px-6 py-2 rounded-lg shadow-lg sm:h-auto">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <div class="text-[15px] font-semibold capitalize">Prospects last contacted (within interval)
                        </div>
                    </div>
                    <FontAwesomeIcon :icon="faCheckCircle" class="text-xl" />
                </div>
                <div>
                    <div class="text-2xl font-bold">1 Week</div>
                    <div class="text-sm text-white/80">have a 4567 people to send</div>
                </div>
            </div>


            <div :class="'from-blue-500  to-sky-300'"
                class="bg-gradient-to-tr text-white flex flex-col justify-between px-6 py-2 rounded-lg shadow-lg sm:h-auto">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <div class="text-[15px] font-semibold capitalize">Prospects not contacted</div>
                    </div>
                    <FontAwesomeIcon :icon="faCheckCircle" class="text-xl" />
                </div>
                <div>
                    <div class="text-2xl font-bold">-</div>
                    <div class="text-sm text-white/80">have a 4567 people to send</div>
                </div>
            </div>
        </div>
    </div>

    <div v-if="form.recipient_type == 'prospect'" class="card">
        <PickList v-model="products" dataKey="id" breakpoint="1400px">
            <template #option="{ option }">
                {{ option.name }}
            </template>
        </PickList>
    </div>

    <div v-if="form.recipient_type == 'custom'" class="card">
        <div class="p-2 bg-gray-100 rounded-lg">
            <Accordion value="0">
                <AccordionPanel value="0">
                    <AccordionHeader>Tags</AccordionHeader>
                    <AccordionContent>
                        <div>
                            <div class="mb-2">
                                <span class="font-bold text-sm block mb-1">{{ trans("Included Tags") }} :</span>
                                <Multiselect v-model="form[fieldName].recipient_builder_data.tag_ids" mode="tags"
                                    placeholder="Select the tag" valueProp="id" trackBy="name" label="name"
                                    :close-on-select="false" :searchable="true" :caret="false" :options="tags"
                                    noResultsText="No one left. Type to add new one.">

                                    <template
                                        #tag="{ option, handleTagRemove, disabled }: { option: tag, handleTagRemove: Function, disabled: boolean }">
                                        <div class="px-0.5 py-[3px]">
                                            <Tag :theme="option.id" :label="option.name" :closeButton="true"
                                                :stringToColor="true" size="sm"
                                                @onClose="(event) => handleTagRemove(option, event)" />
                                        </div>
                                    </template>
                                </Multiselect>
                            </div>

                            <div v-if="form[fieldName].recipient_builder_data?.tag_ids?.length > 1" class="mb-4">
                                <div class="mt-1">
                                    <fieldset>
                                        <div class="space-y-4 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                            <div v-for="(filter, filterIndex) in logic" :key="filter.value"
                                                class="flex items-center">
                                                <input :id="filter.value" :name="'logic' + fieldName" type="radio"
                                                    :value="filter.value"
                                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                                    v-model="form[fieldName].recipient_builder_data.logic" />
                                                <label :for="filter.value"
                                                    class="ml-3 block text-xs font-medium leading-6 text-gray-900">{{
                                                        filter.label }}</label>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <div>
                                <span class="font-bold text-sm block mb-2">{{ trans("Tags not included") }} :</span>
                                <Multiselect v-model="form[fieldName].recipient_builder_data.negative_tag_ids"
                                    mode="tags" placeholder="Select the tag" valueProp="id" trackBy="name" label="name"
                                    :close-on-select="false" :searchable="true" :caret="false" :options="tags"
                                    noResultsText="No one left. Type to add new one.">

                                    <template
                                        #tag="{ option, handleTagRemove, disabled }: { option: tag, handleTagRemove: Function, disabled: boolean }">
                                        <div class="px-0.5 py-[3px]">
                                            <Tag :theme="option.id" :label="option.name" :closeButton="true"
                                                :stringToColor="true" size="sm"
                                                @onClose="(event) => handleTagRemove(option, event)" />
                                        </div>
                                    </template>
                                </Multiselect>
                            </div>

                        </div>
                    </AccordionContent>
                </AccordionPanel>
                <AccordionPanel value="1">
                    <AccordionHeader>Prospect Last Contacted</AccordionHeader>
                    <AccordionContent>
                        <div>
                            <fieldset>
                                <div class="space-y-4 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                    <div v-for="(item, index) in contact" :key="item.value" class="flex items-center">
                                        <input :id="item.value" :name="'logic' + fieldName" type="radio"
                                            :value="item.value"
                                            class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                            v-model="form[fieldName].recipient_builder_data.state" />
                                        <label :for="item.value"
                                            class="ml-3 block text-xs font-medium leading-6 text-gray-900">{{ item.label }}</label>
                                    </div>
                                </div>
                            </fieldset>

                            <div v-if="form[fieldName].recipient_builder_data?.state" class="flex flex-col gap-y-2 mt-4">
                                    <div class="flex gap-x-2">
                                        <div class="w-20">
                                            <PureInput 
                                                type="number" 
                                                :minValue="1" 
                                                :caret="false" 
                                                placeholder="range"
                                                v-model="form[fieldName].recipient_builder_data.argument.quantity" 
                                            />
                                        </div>
                                        <div class="w-full">
                                            <PureMultiselect 
                                                v-model="form[fieldName].recipient_builder_data.argument.unit" 
                                                :options="['day', 'week', 'month']" 
                                                required 
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </AccordionContent>
                </AccordionPanel>
            </Accordion>
        </div>
    </div>
</template>