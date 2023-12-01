<script setup lang="ts">
import descriptor from '../descriptor'
import Multiselect from "@vueform/multiselect"
import axios from "axios"
import Tag from "@/Components/Tag.vue"
import { notify } from "@kyvg/vue3-notification"
import { ref, onMounted, watch, reactive } from 'vue'


const props = withDefaults(defineProps<{
    value: any
    fieldName: any
}>(), {})

const tagsOptions = ref([])

const getTagsOptions = async () => {
    try {
        const response = await axios.get(
            route('org.json.tags'),
        )
        tagsOptions.value = Object.values(response.data)
    } catch (error) {
        notify({
            title: "Failed",
            text: "Failed to get data, Tags, please reload you page",
            type: "error"
        });
    }
}

onMounted(() => {
    getTagsOptions()
})
</script>

<template>
    <div>
        <div>
            <Multiselect v-model="value[fieldName].tag_ids" mode="tags" placeholder="Select the tag" valueProp="id"
                trackBy="name" label="name" :close-on-select="false" :searchable="true" :caret="false"
                :options="tagsOptions" noResultsText="No one left. Type to add new one.">

                <template
                    #tag="{ option, handleTagRemove, disabled }: { option: tag, handleTagRemove: Function, disabled: boolean }">
                    <div class="px-0.5 py-[3px]">
                        <Tag :theme="option.id" :label="option.name" :closeButton="true" :stringToColor="true" size="sm"
                            @onClose="(event) => handleTagRemove(option, event)" />
                    </div>
                </template>
            </Multiselect>
        </div>
        <div v-if="value[fieldName].tag_ids.length > 1" class="mb-4">
            <div class="mt-1">
                <fieldset>
                    <div class="space-y-4 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                        <div v-for="(filter, filterIndex) in descriptor.logic" :key="filter.value"
                            class="flex items-center">
                            <input :id="filter.value" :name="'logic' + fieldName" type="radio" :value="filter.value"
                                class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                v-model="value[fieldName].logic" />
                            <label :for="filter.value" class="ml-3 block text-xs font-medium leading-6 text-gray-900">{{filter.label}}</label>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</template>

<style></style>

