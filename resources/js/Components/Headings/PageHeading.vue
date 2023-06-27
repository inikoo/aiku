<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Fri, 07 Oct 2022 09:34:00 Central European Summer Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>
import { Link } from "@inertiajs/vue3";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faEmptySet, faPeopleArrows } from "@/../private/pro-light-svg-icons";
import { faPencil, faArrowLeft, faBorderAll, faTrashAlt } from "@/../private/pro-regular-svg-icons";

import { faPlus } from "@/../private/pro-solid-svg-icons";
import Button from "@/Components/Elements/Buttons/Button.vue";
import { capitalize } from "@/Composables/capitalize";
import { useLocaleStore } from "@/Stores/locale.js";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { trans } from "laravel-vue-i18n";

library.add(faEmptySet, faPeopleArrows, faPlus, faPencil, faArrowLeft, faBorderAll, faTrashAlt);
const props = defineProps(["data"]);
const locale = useLocaleStore();

const getActionLabel = function(action) {
    if (action.hasOwnProperty("label")) {
        return action.label;
    } else {
        switch (action.style) {
            case "edit":
                return trans("edit");
                break;
            default:
                return "";
        }
    }
};

const getActionIcon = function(action) {
    if (action.hasOwnProperty("icon")) {
        return action.label;
    } else {
        switch (action.style) {
            case "edit":
                return ["far", "fa-pencil"];
                break;
            case "create":
                return ["fal", "fa-plus"];
                break;
            default:
                return null;
        }
    }
};

</script>
<template>
    <div class="mx-4 my-4 md:my-2 grid grid-flow-col justify-between items-center">
        <div>
            <h2 class="font-bold text-gray-900 text-2xl tracking-tight capitalize">

                <span v-if="data.container" class="text-indigo-500 font-medium mr-2">
                    <FontAwesomeIcon v-if="data.container.icon" :title="capitalize(data.container.tooltip)" aria-hidden="true"
                                     :icon="data.container.icon" size="xs" />
                    {{ data.container.label }}
                </span>

                <FontAwesomeIcon v-if="data.icon" :title="capitalize(data.icon.title)" aria-hidden="true"
                                 :icon="data.icon.icon" size="xs" class="pr-2" />
                {{ data.title }}
            </h2>
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                    <div v-for="item in data.meta" :key="item.name"
                         class="mt-2 flex items-center text-sm text-gray-500">
                        <FontAwesomeIcon
                            v-if="item['leftIcon']"
                            :title="capitalize(item['leftIcon']['tooltip'])"
                            aria-hidden="true"
                            :icon="item['leftIcon']['icon']"
                            size="lg"
                            class="text-gray-400 pr-2" />
                        <Link v-if="item.href" :href="route(item.href[0],item.href[1])">
                            <span v-if="item.number">{{ locale.number(item.number) }}</span>
                            <FontAwesomeIcon v-else icon="fal fa-empty-set" />
                            {{ item.name }}
                        </Link>
                        <template v-else-if="item['emptyWithCreateAction']">
                            <FontAwesomeIcon icon="fal fa-empty-set" class="mr-2" />
                            <Button type="primary" size="xs" action="create">
                                {{ item["emptyWithCreateAction"]["label"] }}
                            </Button>
                        </template>
                        <span v-else><span v-if="item.number">{{ locale.number(item.number) }}</span> {{ item.name }} </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Button Add -->
        <div class="flex items-center gap-2">

            <span v-for="action in data.actions">
                 <Link v-if="action.type==='button'" :href="route(action['route']['name'],action['route']['parameters'])">
                         <Button
                             size="xs"
                             type="button"
                             class="inline-flex items-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <FontAwesomeIcon v-if="getActionIcon(action)" :icon="getActionIcon(action)" class="-ml-1 mr-2 text-gray-500" aria-hidden="true" />
                        {{ getActionLabel(action) }}
                     </Button>
                </Link>
            </span>

            <span v-if="data['delete']">
                <Link as="button" :href="route(data['delete']['route']['name'],data['delete']['route']['parameters'])">
                    <FontAwesomeIcon class="text-red-500 hover:text-red-700 mr-3" icon="far fa-trash-alt" />
                </Link>
            </span>

            <span v-if="data['edit']">
                <Link :href="route(data['edit']['route']['name'],data['edit']['route']['parameters'])">
                    <Button
                        size="xs"
                        type="button"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <FontAwesomeIcon icon="far fa-pencil" class="-ml-1 mr-2 text-gray-500" aria-hidden="true" />
                        {{ trans("Edit") }}
                    </Button>
                </Link>
            </span>

            <!-- If the button is more than 1 in one component -->
            <div v-if="data['create'] && data['create']['withMulti']" class="flex">
                <Link :href="route(data['create']['withMulti']['route']['name'],data['create']['withMulti']['route']['parameters'])">
                    <Button type="secondary" class="capitalize rounded-r-none">
                        <FontAwesomeIcon icon="far fa-border-all" class="text-xl" />
                    </Button>
                </Link>
                <Link :href="route(data['create']['route']['name'], data['create']['route']['parameters'])">
                    <Button button="xs" type="secondary" action="create" class="capitalize rounded-l-none text-sm">
                        {{ data["create"]["label"] }}
                    </Button>
                </Link>
            </div>


            <span v-if="data['create'] && !data['create']['withMulti']">
                <Link :href="route(data['create']['route']['name'], data['create']['route']['parameters'])">
                   <Button size="xs" type="secondary" action="create" class="capitalize">
                      {{ data["create"]["label"] }}
                   </Button>
                </Link>
            </span>

            <span v-if="data['create_direct']">
                <Link as="button" method="post" :href="route(data['create_direct']['route']['name'],data['create_direct']['route']['parameters'])">
                    <Button type="secondary" action="create" class="capitalize">
                        {{ data["create_direct"]["label"] }}
                    </Button>
                </Link>
            </span>

            <span v-if="data['cancelCreate']">
                <Link :href="route(data['cancelCreate']['route']['name'],data['cancelCreate']['route']['parameters'])">
                    <Button type="danger" action="cancel" class="capitalize">
                        {{ trans("Cancel") }}
                    </Button>
                </Link>
            </span>

            <span v-if="data['exitEdit']">
                <Link :href="route(data['exitEdit']['route']['name'],data['exitEdit']['route']['parameters'])">
                    <Button type="button"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <FontAwesomeIcon icon="far fa-arrow-left" class="-ml-1 mr-2 h-5 w-5 text-gray-500" aria-hidden="true" />
                        <span v-if="data['exitEdit']['label']">{{ trans(data["exitEdit"]["label"]) }}</span>
                        <span v-if="!data['exitEdit']['label']"> {{ trans("Exit edit") }}</span>
                    </Button>
                </Link>
            </span>

            <span v-if="data['clearMulti']">
                <Link :href="route(data['clearMulti']['route']['name'], data['clearMulti']['route']['parameters'])">
                    <Button type="button"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <FontAwesomeIcon icon="far fa-trash-alt" class="h-5 w-5 text-gray-500" aria-hidden="true" />
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
    <hr />
</template>

<style>
</style>


