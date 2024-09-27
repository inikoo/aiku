<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 26 Sept 2024 22:04:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Button from '@/Components/Elements/Buttons/Button.vue'
import { Button as ButtonTS } from '@/types/Button'
import { routeType } from '@/types/route'
import { Link } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'

const props = defineProps<{

}>()


</script>

<template>

  <main class="grid min-h-full place-items-center bg-white px-6 py-24 sm:py-32 lg:px-8">
    <div class="text-center">
      <p class="text-base font-semibold text-indigo-600">404</p>
      <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl">{{ trans('Page not found')}}</h1>
      <p class="mt-6 text-base leading-7 text-gray-600">{{  trans('Sorry, we could not find the page you’re looking for.') }}</p>
    </div>
  </main>
</template>