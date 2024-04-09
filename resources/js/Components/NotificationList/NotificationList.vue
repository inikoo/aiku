<script setup lang='ts'>
import { faEnvelope, faEnvelopeOpenText } from "@fal";
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { useFormatTime } from '@/Composables/useFormatTime'

const props = defineProps<{
    messages: any
}>()

library.add(faEnvelope, faEnvelopeOpenText);

const messages = props.messages.data

</script>

<template>
    <div class="overflow-auto min-h-11 max-h-96">
      <ul role="list" class="divide-y divide-gray-100 overflow-hidden bg-white">
        <li v-for="message in messages" :key="message.id" class="relative flex justify-between gap-x-6 px-1 py-2 hover:bg-gray-50 sm:px-2">
          <div class="flex min-w-full gap-x-4">
            <font-awesome-icon :icon="message.read ? ['fal', 'envelope-open-text'] : ['fal', 'envelope']" :class="['h-8 w-8 flex-none m-auto', message.read && 'text-gray-400']" />
            <div class="min-w-0 flex-auto relative">
              <p :class="['text-sm font-semibold leading-6', message.read ? 'text-gray-400' : 'text-gray-900']">
                <a :href="message.href">
                  <span :class="['absolute inset-x-0 -top-px bottom-0']"></span>
                  {{ message.title }}
                </a>
              </p>
              <span class="text-[10px] text-gray-500 absolute top-0 right-0 mt-1 mr-1">{{useFormatTime(message.created_at)}}</span>
              <p :class="['mt-1 flex text-xs leading-5', message.read ? 'text-gray-400' : 'text-gray-500']">
                <span :name="message.body" class="relative truncate hover:underline">{{ message.body }}</span>
              </p>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </template>
