<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Thu, 25 May 2023 15:03:05 Central European Summer Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import ShowcaseStats from '@/Components/ShowcaseStats.vue'
import ShowcaseContactCard from "@/Components/ShowcaseContactCard.vue";
import FieldForm from "@/Components/Forms/FieldForm.vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

const props = defineProps<{
  data: {
    blueprint:{
        title:string
        icon:Array<string>
    },
    current:string,

    contactCard: {
    },
    stats: [{
      label: string,
      value: number,
    }],
  },

}>()


</script>

<template >


      <div class="rounded-lg bg-white shadow">
          <div class="divide-y divide-gray-200 lg:grid grid-flow-col lg:grid-cols-12 lg:divide-y-0 lg:divide-x">

              <!-- Left Tab: Navigation -->
              <aside class="py-0 lg:col-span-3 lg:h-full">
                  <nav role="navigation" class="space-y-1">
                      <ul>
                          <li v-for="(item, key) in data.blueprint" @click="data.current = key" :class="[
                            key == data.current
                                ? 'bg-indigo-50 border-indigo-500 text-indigo-700 hover:bg-indigo-50 hover:text-indigo-700'
                                : 'border-transparent text-gray-900 hover:bg-gray-50 hover:text-gray-900',
                            'cursor-pointer group border-l-4 px-3 py-2 flex items-center text-sm font-medium',
                        ]" :aria-current="key === data.current ? 'page' : undefined">
                              <FontAwesomeIcon v-if="item.icon" aria-hidden="true" :class="[
                                key === data.current
                                    ? 'text-indigo-500 group-hover:text-indigo-500'
                                    : 'text-gray-400 group-hover:text-gray-500',
                                'flex-shrink-0 -ml-1 mr-3 h-6 w-6',
                            ]" :icon="item.icon" />

                              <span class="capitalize truncate">{{ item.title }}</span>
                          </li>
                      </ul>
                  </nav>
              </aside>

              <!-- Content of forms -->
              <div class="px-4 sm:px-6 md:px-4 col-span-9">
                  <div class="divide-y divide-grey-200">


                  </div>
              </div>
          </div>
      </div>


</template>

