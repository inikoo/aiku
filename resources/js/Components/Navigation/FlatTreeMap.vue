<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 14 Sept 2022 18:26:10 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup>
import { Link } from '@inertiajs/vue3';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faEmptySet } from '@/../private/pro-light-svg-icons';
library.add(faEmptySet);
defineProps(['nodes']);
import { useLocaleStore } from '@/Stores/locale.js';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
const locale = useLocaleStore();

const dummyLink = "procurement.supplier-deliveries.index"

</script>

<template>
    <nav aria-label="Progress">
        <ol role="list" class="divide-y divide-gray-300 rounded-md border border-gray-300 sm:flex sm:divide-y-0">
            <li v-for="(node, nodeIdx) in nodes" :key="node.name" class="relative sm:flex sm:flex-1">

                <Link :href="route(node.href[0], node.href[1])" class="group flex-1 items-center">
                <span class="grid grid-flow-col justify-between items-center px-6 py-4 text-lg font-medium">
                    <div>
                        <FontAwesomeIcon size="lg" :icon="node.icon" class="flex-shrink-0 " aria-hidden="true" />
                        <span class="ml-4 capitalize font-medium text-gray-500 group-hover:text-gray-900">
                            {{ node.name }}
                            <!-- {{ node.href[0] }} -->
                        </span>
                        <!-- Bars and count -->
                        <span v-if="node.index"
                            class="ml-4 font-medium text-gray-500 group-hover:text-gray-900 whitespace-nowrap">
                            <FontAwesomeIcon icon="fal fa-bars" class="mr-1" />
                            <span v-if="node.index.number">{{ locale.number(node.index.number) }}</span>
                            <FontAwesomeIcon v-else icon="fal fa-empty-set" />
                        </span>
                    </div>

                    <!-- Sublink on right each section -->
                    <div class="relative">
                        <a @click.prevent="() => { $inertia.visit(route(dummyLink)) }"
                            class="w-8 h-8 grid justify-center items-center border-2 text-indigo-500 border-indigo-500 rounded-lg cursor-pointer hover:bg-indigo-500 hover:text-white transition-all duration-75 ease-in-out">
                            <FontAwesomeIcon size="md" :icon="node.icon" class="flex-shrink-0 " aria-hidden="true" />
                        </a>
                        <!-- <div @click.prevent="" class="absolute py-1 px-2 text-[5px] inline bg-red-500">
                            This is a button for {{ node.name }}
                        </div> -->
                    </div>
                </span>
                </Link>

                <template v-if="nodeIdx !== nodes.length - 1">
                    <!-- Arrow separator for lg screens and up -->
                    <div class="absolute top-0 right-0 hidden h-full w-5 sm:block" aria-hidden="true">
                        <svg class="h-full w-full text-gray-300" viewBox="0 0 22 80" fill="none" preserveAspectRatio="none">
                            <path d="M0 -2L20 40L0 82" vector-effect="non-scaling-stroke" stroke="currentcolor"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                </template>
            </li>
        </ol>
    </nav>
</template>

