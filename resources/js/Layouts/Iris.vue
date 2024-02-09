<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 08 Feb 2024 16:53:54 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import Header from '@/Layouts/Iris/Header.vue'


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
