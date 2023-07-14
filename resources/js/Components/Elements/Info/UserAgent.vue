<!--
  - Author: Artha <artha@aw-advantage.com>
  - Created: Wed, 07 Jun 2023 09:03:33 Central Indonesia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { capitalize } from "@/Composables/capitalize"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

const props = defineProps<{
    data: string[]
}>()
console.log(typeof props.data)

const deviceType = props.data[0]
const platformName = props.data[1]
const browserName = props.data[2].split(" ")[0]

const newData = JSON.parse(props.data)

// const deviceIcon = '/devices/' + deviceType.toLowerCase() + '.png';
// const platformIcon = '/os/' + platformName.toLowerCase() + '.png';
// const browserIcon = '/browsers/' + browserName.toLowerCase() + '.png';

</script>

<template>
    <div class="flex">
        <FontAwesomeIcon :icon='data[0].icon' class='w-6' aria-hidden='true' :title='capitalize(newData[0].title)' />
        <FontAwesomeIcon :icon='data[1].icon' class='w-6' aria-hidden='true' :title='capitalize(newData[1].title)' />
        <FontAwesomeIcon :icon='data[2].icon' class='w-6' aria-hidden='true' :title='capitalize(newData[2].title)' />
    </div>
    <!-- <img class="inline pr-2 w-6" :src='deviceIcon' :alt="deviceType" :title='capitalize(deviceType)' />
    <img class="inline pr-2 w-6" :src='platformIcon' :alt="platformName" :title='capitalize(platformName)' />
    <img class="inline pr-1 w-6" :src='browserIcon' :alt="browserName" :title='capitalize(browserName)' /> -->
</template>
