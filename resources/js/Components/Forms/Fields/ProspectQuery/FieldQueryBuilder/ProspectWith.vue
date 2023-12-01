<script setup lang="ts">
import descriptor from '../descriptor'

const props = withDefaults(defineProps<{
    value: any
    fieldName : any
}>(), {})
</script>

<template>
    <div class="flex flex-wrap items-center">
        <div v-for="(query, index) in descriptor.queryLists" :key="query.value"
            class="flex items-center mr-4 mb-2 py-[4px] px-2.5 border border-solid border-gray-300 rounded-lg">
            <input type="checkbox" v-model="value[fieldName].fields" :id="query.value" :key="query.value"
                :value="query.value" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
            <label :for="query.value" class="ml-2">{{ query.label }}</label>
        </div>
    </div>
    <div v-if="value[fieldName].fields.length > 1" class="mb-4">
            <div class="mt-1">
                <fieldset>
                    <div class="space-y-4 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                        <div v-for="(filter, filterIndex) in descriptor.logic" :key="filter.value + filterIndex"
                            class="flex items-center">
                            <input :id="filter.value + filterIndex" :name="'logic' + fieldName" type="radio" :value="filter.value"
                                class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                v-model="value[fieldName].logic" />
                            <label :for="filter.value + filterIndex" class="ml-3 block text-xs font-medium leading-6 text-gray-900">{{filter.label}}</label>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
</template>

