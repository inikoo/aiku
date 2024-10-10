<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { getStyles } from "@/Composables/styles";
import { faCube, faStar } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"


library.add(faCube, faStar)

const props = defineProps<{
  modelValue: any
  properties: {}
}>()


</script>

<template>
  <div class="w-full py-12 px-8 flex gap-x-10">
    TEST
  </div>
</template>