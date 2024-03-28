<script setup>
import { faEnvelope, faEnvelopeOpenText } from "@fal";
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { useFormatTime } from '@/Composables/useFormatTime'

library.add(faEnvelope, faEnvelopeOpenText);

const messages = [
    {
        title: 'Leslie Alexander',
        message: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: false
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@examplee.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: false
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@examsple.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: true
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@exampdle.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: false
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@exampdle.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: true
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@exampdle.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: false
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@exampdle.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: true
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@exampdle.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: false
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@exampdle.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: true
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@exampdle.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: false
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@exampdle.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: true
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@exampdle.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: true
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@exampdle.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: true
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@exampdle.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: true
    },
    {
        title: 'Leslie Alexander',
        message: 'leslie.alexander@exampdle.com',
        href: '#',
        recived_date: '2023-01-23T13:23Z',
        read: true
    },
]

</script>

<template>
    <div class="overflow-auto min-h-11 max-h-96">
      <ul role="list" class="divide-y divide-gray-100 overflow-hidden bg-white">
        <li v-for="message in messages" :key="message.message" class="relative flex justify-between gap-x-6 px-1 py-2 hover:bg-gray-50 sm:px-2">
          <div class="flex min-w-full gap-x-4">
            <font-awesome-icon :icon="message.read ? ['fal', 'envelope-open-text'] : ['fal', 'envelope']" :class="['h-8 w-8 flex-none m-auto', message.read && 'text-gray-400']" />
            <div class="min-w-0 flex-auto relative">
              <p :class="['text-sm font-semibold leading-6', message.read ? 'text-gray-400' : 'text-gray-900']">
                <a :href="message.href">
                  <span :class="['absolute inset-x-0 -top-px bottom-0']"></span>
                  {{ message.title }}
                </a>
              </p>
              <span class="text-[10px] text-gray-500 absolute top-0 right-0 mt-1 mr-1">{{useFormatTime(message.recived_date)}}</span>
              <p :class="['mt-1 flex text-xs leading-5', message.read ? 'text-gray-400' : 'text-gray-500']">
                <span :name="message.message" class="relative truncate hover:underline">{{ message.message }}</span>
              </p>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </template>