
<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 08 Sept 2022 00:38:38 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <div>
        <div class="m-4 bg-white shadow sm:rounded-lg max-w-2xl">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ trans('App subscription')}}</h3>
                <div class="mt-2 sm:flex sm:items-start sm:justify-between">
                    <div class="max-w-xl text-sm text-gray-500">
                        <p class="text-red-500">{{ trans('This employee is not an user') }}.</p>
                    </div>
                    <div class="mt-5 sm:mt-0 sm:ml-6 sm:flex sm:flex-shrink-0 sm:items-center">
                        <button @click="createEmployeeUser" type="button" class="mr-5 inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:text-sm">
                            {{ trans('Add employee to system users') }}
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>



</template>

<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import {library} from '@fortawesome/fontawesome-svg-core';
import {faIdCard} from '@/../private/pro-light-svg-icons';
import PageHeading from '@/Components/Headings/PageHeading.vue';

library.add(faIdCard)
import { trans } from 'laravel-vue-i18n';
import {Inertia} from '@inertiajs/inertia';

defineProps(["title","pageHead","employee"])

const createEmployeeUser = () =>{
    console.log('create employee user')
    Inertia.post('/users', {
        name: 'John Doe',
        email: 'john.doe@example.com',
    })
}

</script>
