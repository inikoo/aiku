<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 19 Aug 2021 18:54:53 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2021, Inikoo
  -  Version 4.0
  -->
<script setup lang="ts">
import {computed} from 'vue';
import {Link} from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'


const props = defineProps<{
    breadcrumbs: Array<{
        type:string,
        simple:{
            icon?:string,
            overlay?:string,
            label?:string,
            route?:{
                name:string,
                parameters?:Array<string>
            }
        },
        modelWithIndex:{
            index:{
                icon?:string,
                label?:string,
                route?:{
                    name:string,
                    parameters?:Array<string>
                }
            },
            model:{
                icon?:string,
                label?:string,
                route?:{
                    name:string,
                    parameters?:Array<string>
                }
            },
        }
        suffix?:string,
        options?:object



    }>
}>()

const displayBreadcrumbs = computed(() => {
    return Object.keys(props['breadcrumbs']).length > 0;
});

</script>

<template>
    <div v-if="displayBreadcrumbs">
        <nav class="hidden sm:flex text-gray-600 bg-white border-b h-8 border-gray-200 " aria-label="Breadcrumb">
            <ol role="list" class="w-full mx-auto px-4 flex space-x-4 ">

                <li v-for="(breadcrumb, breadcrumbIdx) in  breadcrumbs" :key="breadcrumbIdx" class="flex">
                    <div class="flex items-center">
                        <font-awesome-icon  v-if="breadcrumbIdx!==0"  class="flex-shrink-0 h-5 w-5 mr-2" icon="fa-regular fa-chevron-right" aria-hidden="true"/>

                        <template v-if="breadcrumb.type==='simple'">

                            <component :is="breadcrumb.simple.route?Link:'span'"  :class="'hover:text-gray-700' || ''"
                                       :href=" breadcrumb.simple.route?   route(breadcrumb.simple.route.name,breadcrumb.simple.route.parameters) :''" >
                                <font-awesome-icon  v-if="breadcrumb.simple.icon"  :class="breadcrumb.simple.label?'mr-2':''"  class="flex-shrink-0 h-4 w-4" :icon="breadcrumb.simple.icon" aria-hidden="true"/>
                                <span class="capitalize">{{breadcrumb.simple.label}}</span>
                            </component>


                        </template>
                        <template v-if="breadcrumb.type==='modelWithIndex'">

                            <Link class="mr-1 hover:text-gray-700"  :href="route(breadcrumb.modelWithIndex.index.route.name,breadcrumb.modelWithIndex.index.route.parameters)">
                                <font-awesome-icon
                                    :icon="['fal', 'bars']"
                                    class="flex-shrink-0 h-4 w-4 mr-1"
                                    aria-hidden="true"
                                />
                                <span class="capitalize text-sm	">{{breadcrumb.modelWithIndex.index.label}}</span>
                            </Link>→
                            <Link class="ml-1  text-indigo-400 hover:text-indigo-500"  :href="route(breadcrumb.modelWithIndex.model.route.name,breadcrumb.modelWithIndex.model.route.parameters)">
                                <span class="capitalize   ">{{breadcrumb.modelWithIndex.model.label}}</span>
                            </Link>

                        </template>
                        <span  :class="breadcrumb.type?'ml-1':''"  v-show="breadcrumb.suffix" class="italic">{{breadcrumb.suffix}}</span>



                    </div>
                </li>
            </ol>
        </nav>
    </div>
</template>

