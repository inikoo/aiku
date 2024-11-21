<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { getStyles } from "@/Composables/styles"

library.add(faCube, faLink)

const props = defineProps<{
	modelValue: any
	webpageData: any
	blockData: Object
}>()

</script>

<template>
	<div :style="getStyles(modelValue.container.properties)">
        <div class="w-full">
            <div class="relative isolate overflow-hidden px-6 py-24 text-center shadow-2xl sm:px-16">
				<section v-html="modelValue.title"></section>
				<section v-html="modelValue.text"></section>

                <div class="flex justify-center">
                    <div typeof="button" :style="getStyles(modelValue.button.container.properties)"
                        class="mt-10 flex items-center justify-center w-64 mx-auto gap-x-6">
                        {{modelValue.button.text}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
