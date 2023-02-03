<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Fri, 12 Aug 2022 15:44:32 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Inikoo
  -  Version 4.0
  -->

<template>

    <div  class="max-w-lg mx-auto mt-8">
        <div>
            <div class="text-center">
                <span class="mx-auto text-xl	">
                    👍 Excellent choice <i>{{$page.props.auth.user.username}}</i>
                </span>

                <h2 class="mt-2 text-lg font-medium text-gray-900">Now we need to link to your organization <i>system software</i></h2>
                <p class="mt-1 text-sm text-gray-500">Ask your manager to provide your access code. Or id you have an Aurora account get it by yourself. <span @click="showInstructions=true;" :class="{hidden: showInstructions}" class="special-underline cursor-pointer">show me the instructions</span></p>

            </div>

            <div :class="{ hidden: !showInstructions}" class="mt-4 m-8 text-sm text-gray-500">
                <ol class="list-decimal">
                    <li>Log in to your aurora account (your <i>sysadmin</i> will need to create one if you dont have one)</li>
                    <li>Go to your profile (Click your name in the top right)</li>

                </ol>
            </div>

            <form @submit.prevent="submitAccessCode" class="mt-6 flex">
                <label for="username" class="sr-only">username</label>
                <input v-model="formAccessCode.code" type="text" name="username" id="username" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Access code" />
                <button type="submit" class="ml-4 flex-shrink-0 px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Finish setup</button>
            </form>
            <ValidationErrors class="mt-4" />
        </div>

    </div>


</template>

<script setup>
import { ref } from 'vue'

import {useForm} from '@inertiajs/vue3';
import ValidationErrors from '@/Components/ValidationErrors.vue';

const formAccessCode = useForm({
                                   code: '',
                     });
const submitAccessCode = () => {
    formAccessCode.post(route('setup.access-code'), {

    });
};
const showInstructions = ref(null)


</script>
