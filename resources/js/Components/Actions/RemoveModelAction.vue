<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 24 Jun 2023 13:56:46 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { trans } from "laravel-vue-i18n";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faTrashAlt } from "@/../private/pro-light-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core";
library.add(faTrashAlt)

const props = defineProps<{
    action: {
        type: string,
        route: {
            name: string
            parameters: string[]
        }
    }

}>()

</script>

<template>


    <div class="bg-white shadow sm:rounded-lg max-w-xl  ">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900">{{trans('Soft delete')}}</h3>
            <div class="mt-2 sm:flex sm:items-start sm:justify-between">
                <div class="max-w-xl text-sm text-gray-500">
                    <p>{{trans('Keep a copy of it, it may be restored but you will be hoarding data')}}</p>
                </div>
                <div class="mt-5 sm:ml-6 sm:mt-0 sm:flex sm:flex-shrink-0 sm:items-center">

                    <Link :href="route(action.route.name,action.route.parameters)" method="delete"  as="button"  type="button" class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">

                        <FontAwesomeIcon aria-hidden="true" icon="fal fa-trash-alt" class="-ml-0.5 mr-1 h-5 w-5" />


                        {{trans('Soft Delete')}}
                    </Link>

                </div>
            </div>
        </div>
    </div>

</template>
