<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 04 Feb 2024 10:09:22 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { usePage } from "@inertiajs/vue3"
import Header from '@/Layouts/AikuPublic/Header.vue'


</script>

<template>
    <div class="relative">
        <div class="container max-w-7xl mx-auto shadow-xl">
            <Header />

            <main
                class="text-gray-700">
                <slot />
            </main>
        </div>
    </div>
</template>