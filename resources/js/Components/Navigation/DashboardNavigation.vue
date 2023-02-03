<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 14 Sept 2022 18:26:10 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup>
import {Link} from '@inertiajs/vue3';
import {library} from '@fortawesome/fontawesome-svg-core';
import {faEmptySet} from '@/../private/pro-light-svg-icons';
library.add(faEmptySet);
defineProps(['nodes']);
import {useLocaleStore} from '@/Stores/locale.js';
const locale = useLocaleStore();

</script>

<template>
    <nav aria-label="Progress">
        <ol role="list" class="divide-y divide-gray-300 rounded-md border border-gray-300 sm:flex sm:divide-y-0">
            <li v-for="(node, nodeIdx) in nodes" :key="node.name" class="relative sm:flex sm:flex-1">

                <Link :href="route(node.href[0],node.href[1])" class="group flex items-center">
                    <span class="flex items-center px-6 py-4 text-lg font-medium">
                        <font-awesome-icon size="lg"
                                           :icon="node.icon"
                                           class="flex-shrink-0 "
                                           aria-hidden="true"
                        />
                        <span class="ml-4 capitalize font-medium text-gray-500 group-hover:text-gray-900">
                            {{ node.name }}
                        </span>
                         <span v-if="node.index" class="ml-4 font-medium text-gray-500 group-hover:text-gray-900 whitespace-nowrap">
                           <font-awesome-icon icon="fal fa-bars" class="mr-1"/>
                             <span v-if="node.index.number">{{ locale.number(node.index.number) }}</span>
                             <font-awesome-icon v-else icon="fal fa-empty-set"/>

                        </span>

                    </span>
                </Link>
                <template v-if="nodeIdx !== nodes.length - 1">
                    <!-- Arrow separator for lg screens and up -->
                    <div class="absolute top-0 right-0 hidden h-full w-5 sm:block" aria-hidden="true">
                        <svg class="h-full w-full text-gray-300" viewBox="0 0 22 80" fill="none" preserveAspectRatio="none">
                            <path d="M0 -2L20 40L0 82" vector-effect="non-scaling-stroke" stroke="currentcolor" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </template>
            </li>
        </ol>
    </nav>
</template>

