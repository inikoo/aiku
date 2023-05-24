<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Fri, 07 Oct 2022 09:34:00 Central European Summer Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>
import {Link} from '@inertiajs/vue3';
import {library} from '@fortawesome/fontawesome-svg-core';
import {faEmptySet} from '@/../private/pro-light-svg-icons';
import {faPencil,faArrowLeft} from '@/../private/pro-regular-svg-icons';

import {faPlus} from '@/../private/pro-solid-svg-icons';
import Button from '@/Components/Elements/Buttons/Button.vue';
import {useLocaleStore} from '@/Stores/locale.js';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {trans} from 'laravel-vue-i18n';

library.add(faEmptySet, faPlus, faPencil,faArrowLeft);
const props = defineProps(['data']);
const locale = useLocaleStore();


</script>
<template>
    <div class="mx-4 my-4 md:my-2 grid grid-flow-col justify-between items-center">
        <div class="">
            <h2 class="font-bold text-gray-900 text-2xl tracking-tight capitalize">

                <span v-if="data.container" class="text-indigo-500 font-medium mr-2">
                    <FontAwesomeIcon v-if="data.container.icon" :title="data.container.tooltip" aria-hidden="true"
                                     :icon="data.container.icon" size="xs" />
                    {{ data.container.label }}
                </span>

                <FontAwesomeIcon v-if="data.icon" :title="data.icon.title" aria-hidden="true"
                                 :icon="data.icon.icon" size="xs" class="pr-2"/>
                {{ data.title }}
            </h2>
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                    <div v-for="item in data.meta" :key="item.name"
                         class="mt-2 flex items-center text-sm text-gray-500">
                        <FontAwesomeIcon
                            v-if="item['leftIcon']"
                            :title="item['leftIcon']['tooltip']"
                            aria-hidden="true"
                            :icon="item['leftIcon']['icon']"
                            size="lg"
                            class="text-gray-400 pr-2"/>
                        <Link v-if="item.href" :href="route(item.href[0],item.href[1])">
                            <span v-if="item.number">{{ locale.number(item.number) }}</span>
                            <FontAwesomeIcon v-else icon="fal fa-empty-set"/>
                            {{ item.name }}
                        </Link>
                        <template v-else-if="item['emptyWithCreateAction']">
                            <FontAwesomeIcon icon="fal fa-empty-set" class="mr-2"/>

                                <Button type="primary" size="xs" action="create">
                                    {{ item['emptyWithCreateAction']['label'] }}</Button>

                        </template>
                        <span v-else><span v-if="item.number">{{ locale.number(item.number) }}</span> {{ item.name }} </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Button Add -->
        <div class="">
             <span v-if="data['edit']"  class="">
                <Link :href="route(data['edit']['route']['name'],data['edit']['route']['parameters'])">
                <Button type="button"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <FontAwesomeIcon icon="far fa-pencil" class="-ml-1 mr-2 h-5 w-5 text-gray-500" aria-hidden="true"/>
                    {{ trans('Edit') }}
                </Button>
                </Link>
            </span>

            <span v-if="data['create']" class="">
                <Link :href="route(data['create']['route']['name'],data['create']['route']['parameters'])">
                <Button type='secondary' action="create" class="capitalize">
                 {{data['create']['label']}}
                </Button>
                </Link>
            </span>

            <span v-if="data['create_direct']" class="">
                <Link as="button" method="post" :href="route(data['create_direct']['route']['name'],data['create_direct']['route']['parameters'])">
                <Button type='secondary' action="create" class="capitalize">
                 {{data['create_direct']['label']}}
                </Button>
                </Link>
            </span>

            <span v-if="data['cancelCreate']" class="">
                <Link :href="route(data['cancelCreate']['route']['name'],data['cancelCreate']['route']['parameters'])">
                <Button type='secondary' action="cancel" class="capitalize">
                   {{ trans('Cancel') }}
                </Button>
                </Link>
            </span>

            <span v-if="data['exitEdit']"  class="">
                <Link :href="route(data['exitEdit']['route']['name'],data['exitEdit']['route']['parameters'])">
                <Button type="button"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <FontAwesomeIcon icon="far fa-arrow-left" class="-ml-1 mr-2 h-5 w-5 text-gray-500" aria-hidden="true"/>
                     {{ trans('Exit edit') }}
                </Button>
                </Link>
            </span>

            <!-- Dropdown -->
            <!-- <Menu as="div" class="relative ml-3 sm:hidden">
                <MenuButton
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    More
                    <FontAwesomeIcon aria-hidden="true" class="-mr-1 ml-2 h-5 w-5 text-gray-500" icon="fa-regular fa-chevron-down"/>

                </MenuButton>

                <transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                    <MenuItems class="absolute left-0 z-10 mt-2 -mr-1 w-48 origin-top-left rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                        <MenuItem v-slot="{ active }">
                            <a href="#" :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']">Edit</a>
                        </MenuItem>
                        <MenuItem v-slot="{ active }">
                            <a href="#" :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']">View</a>
                        </MenuItem>
                    </MenuItems>
                </transition>
            </Menu> -->
        </div>
    </div>
</template>


