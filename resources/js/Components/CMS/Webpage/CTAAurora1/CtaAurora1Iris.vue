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
	fieldValue: {
        
    }
	webpageData?: any
	blockData?: Object
}>()
console.log(props)
</script>

<template>
	<div :style="getStyles(fieldValue.container.properties)">
        <div class="w-full">
            <div class="relative isolate overflow-hidden px-6 py-16 md:py-24 text-center shadow-2xl sm:px-16">
				<section v-html="fieldValue.title"></section>
				<section v-html="fieldValue.text"></section>

                <div class="flex justify-center">
                    <a :href="fieldValue?.button?.link?.href" :target="fieldValue?.button?.link?.target" typeof="button" :style="getStyles(fieldValue.button.container.properties)"
                        class="mt-10 flex items-center justify-center w-64 mx-auto gap-x-6">
                        {{fieldValue.button.text}}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
