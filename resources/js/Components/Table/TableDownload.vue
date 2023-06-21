<script setup>
import { Link } from "@inertiajs/vue3"
import { ref } from "vue"
import { trans } from 'laravel-vue-i18n'

import ButtonWithDropdown from "./ButtonWithDropdown.vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faDownload } from "@/../private/pro-solid-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faDownload)


const download = ref(null)
const urlPage = location.pathname
</script>

<template>
    <ButtonWithDropdown ref="download" dusk="table-download-dropdown" class="w-auto">
        <template #button>
            <div class="h-5 w-5 flex justify-center items-center" :title="trans('Export Table')">
                <FontAwesomeIcon icon="fas fa-download" class="h-4 w-4 text-gray-400" aria-hidden="true" />
            </div>
        </template>

        <!-- The popup -->
        <div role="menu" aria-orientation="horizontal" aria-labelledby="table-download-data" class="grid w-40 min-w-max">
            <a :href="`${urlPage}/export?type=xlsx`"
                :dusk="`add-search-row-1`"
                role="menuitem" download
                class="text-left w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
            >
                Export as Excel (.xlsx)
            </a>
            <a :href="`${urlPage}/export?type=csv`"
                :dusk="`add-search-row-2`"
                role="menuitem" download
                class="text-left w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
            >
                Export as CSV (.csv)
            </a>
        </div>
    </ButtonWithDropdown>
</template>
