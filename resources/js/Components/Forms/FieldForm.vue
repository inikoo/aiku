<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 03:12:13 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import { useForm } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faSave } from '@/../private/pro-light-svg-icons';
import { faAsterisk } from '@/../private/pro-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import Input from '@/Components/Forms/Fields/Input.vue';
import Phone from '@/Components/Forms/Fields/Phone.vue';
import Date from '@/Components/Forms/Fields/Date.vue';
import Theme from '@/Components/Forms/Fields/Theme.vue';
import ColorMode from '@/Components/Forms/Fields/ColorMode.vue';
import Avatar from '@/Components/Forms/Fields/Avatar.vue';
import Password from '@/Components/Forms/Fields/Password.vue'
import Textarea from '@/Components/Forms/Fields/Textarea.vue'
import Toggle from '@/Components/Forms/Fields/Toggle.vue'
import Select from '@/Components/Forms/Fields/Select.vue'
import Radio from '@/Components/Forms/Fields/Radio.vue'
import TextEditor from '@/Components/Forms/Fields/TextEditor.vue'
import Address from "@/Components/Forms/Fields/Address.vue";
import Country from "@/Components/Forms/Fields/Country.vue";
import Currency from "@/Components/Forms/Fields/Currency.vue";
import Language from "@/Components/Forms/Fields/Language.vue";
import Permissions from "@/Components/Forms/Fields/Permissions.vue";
import WebRegistrations from "@/Components/Forms/Fields/WebRegistrations.vue";

import InputWithAddOn from '@/Components/Forms/Fields/InputWithAddOn.vue'

library.add(faSave, faAsterisk);

const props = defineProps<{
    field: string,
    fieldData: {
        type: string,
        label: string,
        value: any,
        required: boolean
        fullComponentArea : boolean

    },
    args: {
        updateRoute: {
            name: string,
            parameters: string | string[]
        }
    }
}>()


const updateRoute = props['fieldData']['updateRoute'] ?? props.args['updateRoute'];

const components = {
    'select': Select,
    'input': Input,
    'inputWithAddOn': InputWithAddOn,
    'phone': Phone,
    'date': Date,
    'theme': Theme,
    'colorMode': ColorMode,
    'password': Password,
    'avatar': Avatar,
    'textarea': Textarea,
    'toggle': Toggle,
    'radio': Radio,
    'textEditor': TextEditor,
    'address': Address,
    'country': Country,
    'currency': Currency,
    'language': Language,
    'permissions': Permissions,
    'webRegistrations': WebRegistrations,
};

const getComponent = (componentName) => {
    return components[componentName] ?? null;
};

let formFields = {
    [props.field]: props.fieldData.value,
};

if (props['fieldData']['hasOther']) {
    formFields[props['fieldData']['hasOther']['name']] = props['fieldData']['hasOther']['value'];
}

formFields['_method'] = 'patch';

const form = useForm(formFields);
form['fieldType'] = 'edit';


const routeUrl=  route(updateRoute.name, updateRoute.parameters);
function submit() {
    form.post(routeUrl,{caca:'xxx'})
}

</script>

<template>
    <form @submit.prevent="submit">
        <dl v-if="!props.fieldData.fullComponentArea" class="divide-y divide-gray-200 max-w-2xl ">
            <div class="pb-4 sm:pb-5 sm:grid sm:grid-cols-3 sm:gap-4 ">
                <dt class="text-sm font-medium text-gray-500 capitalize">
                    <div class="inline-flex items-start leading-none"><FontAwesomeIcon v-if="fieldData.required" :icon="['fas', 'asterisk']" class="font-light text-[12px] text-red-400 mr-1"/>{{ fieldData.label }}</div>
                </dt>
                <dd class="sm:col-span-2  ">
                    <div class="mt-1 flex items-start text-sm text-gray-900 sm:mt-0">
                        <div class="relative  flex-grow">
                            <component :is="getComponent(fieldData['type'])" :form=form :fieldName=field
                                :options="fieldData['options']" :fieldData="fieldData">
                            </component>
                        </div>

                        <!-- Button: Save -->
                        <span class="ml-2 flex-shrink-0">
                            <button class="align-bottom" :disabled="form.processing || !form.isDirty" type="submit">
                                <FontAwesomeIcon icon="fal fa-save" class="h-8 "
                                    :class="form.isDirty ? 'text-indigo-500' : 'text-gray-200'" aria-hidden="true" />
                            </button>
                        </span>
                    </div>
                </dd>
            </div>
        </dl>

        <!-- full area components -->
        <dl v-if="props.fieldData.fullComponentArea" class="divide-y divide-gray-200">
            <dd class="sm:col-span-2  ">
                <div class="mt-1 flex items-start text-sm text-gray-900 sm:mt-0">
                    <div class="relative  flex-grow">
                        <component :is="getComponent(fieldData['type'])" :form=form :fieldName=field
                            :options="fieldData['options']" :fieldData="fieldData">
                        </component>
                    </div>
                </div>
            </dd>
        </dl>

    </form>
</template>
