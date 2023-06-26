<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPlus, faCactus, faIslandTropical, faSkullCow } from "@/../private/pro-light-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faPlus, faCactus, faIslandTropical, faSkullCow)
import {trans} from 'laravel-vue-i18n';


</script>

<template>
    <div class="text-center border-gray-200 pt-14">

        <FontAwesomeIcon icon="fal fa-cactus" class="mx-auto h-9 text-gray-400" aria-hidden="true" />
        <FontAwesomeIcon icon="fal fa-skull-cow" class="mx-7   h-12 w-12 text-gray-500" aria-hidden="true" />
        <FontAwesomeIcon icon="fal fa-cactus" class="mx-auto h-8  text-gray-400" aria-hidden="true" />

        <h3 class="mt-3 text-sm font-semibold text-gray-800">{{trans('No records')}}</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new project.</p>
        <div class="mt-6">
            <button
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <FontAwesomeIcon icon="far fa-plus" class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                New Project
            </button>
        </div>
    </div>
</template>
