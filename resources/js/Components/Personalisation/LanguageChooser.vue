<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Fri, 09 Sept 2022 00:59:50 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<template>
    <Menu as="div" class="relative inline-block text-left">
        <div>
            <MenuButton
                class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-100">
                <font-awesome-icon aria-hidden="true" icon="fa-regular fa-language" size="lg"/>
                <span class="mx-1"> {{ languageCode }}</span>
                <font-awesome-icon aria-hidden="true" icon="fa-regular fa-angle-up"/>
            </MenuButton>
        </div>

        <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems class="sm:absolute sm:bottom-12 sm:right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1">

                    <MenuItem v-for="language in languages" key="language.code" v-slot="{ active }">
                          <span @click="setLanguage(language.code)" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'cursor-pointer  block px-4 py-2 text-sm']">
                              {{ language.name.value }}
                              <span v-if="language.name.value!== language.nativeName" :id="`${language.code}-description`" class="ml-2 text-gray-500">({{
                                      language.nativeName
                                  }})</span>

                          </span>

                    </MenuItem>


                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>

<script setup>
import {library} from '@fortawesome/fontawesome-svg-core';
import {faLanguage} from '@/../private/pro-regular-svg-icons';



import {getActiveLanguage, loadLanguageAsync, wTrans} from 'laravel-vue-i18n';
import {usePage} from '@inertiajs/inertia-vue3';
import VueCookies from 'vue-cookies';
import {Inertia} from '@inertiajs/inertia';

library.add(faLanguage);

import {Menu, MenuButton, MenuItem, MenuItems} from '@headlessui/vue';
import {ref} from 'vue';

const languageCode = ref(getActiveLanguage());

const languages = [
    {
        name      : wTrans('English'),
        nativeName: 'English',
        code      : 'en',
    },
    {
        name      : wTrans('Spanish'),
        nativeName: 'Español',
        code      : 'es',
    },

];

function setLanguage(code) {
    open.value = false;
    loadLanguageAsync(code);
    languageCode.value = code;
    VueCookies.set('language', code, '1y');
    if (usePage().props.value.auth.user) {
        Inertia.patch('/profile', {
            language: code,
        });

    }

}
</script>
