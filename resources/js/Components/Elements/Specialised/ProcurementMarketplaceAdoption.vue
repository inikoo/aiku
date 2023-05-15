<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 15 May 2023 13:13:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from "@fortawesome/fontawesome-svg-core"
import {faCheckCircle,faPlusCircle,faPauseCircle } from "@/../private/pro-solid-svg-icons"

library.add(faCheckCircle,faPlusCircle,faPauseCircle)
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

const props = defineProps<{
    value: string
}>()

</script>

<template>
    <FontAwesomeIcon v-if="value==='adopted'"  icon="fas fa-check-circle" class="mt-1.5  h-5 w-5 text-green-500" aria-hidden="true"/>
    <FontAwesomeIcon v-if="value==='available'"  icon="fas fa-plus-circle" class="mt-1.5  h-5 w-5 text-indigo-200 hover:text-indigo-500" aria-hidden="true"/>
    <FontAwesomeIcon v-if="value==='removed'"  icon="fas fa-pause-circle" class="mt-1.5  h-5 w-5 text-gray-200" aria-hidden="true"/>

</template>


