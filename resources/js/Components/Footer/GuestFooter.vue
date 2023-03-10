<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 08 Sept 2022 23:44:56 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>
import LanguageChooser from '@/Components/Personalisation/LanguageChooser.vue'


</script>

<template>
    <footer class="bg-white">
        <div class="mx-auto max-w-7xl py-12 px-4 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
            <div class="flex justify-center space-x-6 md:order-2">
                <language-chooser/>
            </div>
            <div class="mt-8 md:order-1 md:mt-0">
                <p class="text-center text-base text-gray-400">&copy; {{new Date().getFullYear()}} Inikoo. All rights reserved.</p>
            </div>
        </div>
    </footer>
</template>


