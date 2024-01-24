<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 27 Sep 2023 11:35:29 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import {useLocaleStore} from "@/Stores/locale";
import {capitalize} from "@/Composables/capitalize";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faLevelUp } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faLevelUp)


const props = defineProps<{
   data:object
}>()

const locale = useLocaleStore()

</script>

<template>
    <FontAwesomeIcon v-if="data.icon" :title="capitalize(data.tooltip)"
                     aria-hidden="true" :icon="data.icon" size="xs"/>
    <span class="leading-none mr-1 ml-1">{{ data.label }}</span>
    <FontAwesomeIcon flip="horizontal" :icon="'fal fa-level-up'" :class="'mr-2'" />
</template>
