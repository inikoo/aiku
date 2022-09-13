<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 10 Sept 2022 23:39:37 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<template>
    <div class="m-4 mt-0 lg:flex lg:items-center lg:justify-between">
        <div class="min-w-0 flex-1">
            <h2 :class="[props.data.capitalize??true?'capitalize':'','mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight ']">{{ data.title }}</h2>
        </div>
        <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
            <div v-for="item in data.meta" :key="item.name" class="mt-2 flex items-center text-sm text-gray-500">
                <font-awesome-icon v-if="item['leftIcon']" :title="item['leftIcon'].tooltip" aria-hidden="true" :icon="item['leftIcon'].icon" size="lg" class="pr-2"/>
                {{ item.name }}
            </div>
        </div>
    </div>
</template>
<script setup>
const props=defineProps(['data']);


</script>
