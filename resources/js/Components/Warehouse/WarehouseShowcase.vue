<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 18:37:23 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import SimpleBox from "@/Components/DataDisplay/SimpleBox.vue"
const props = defineProps<{
    data?: {}
}>()
</script>


<template>
    <SimpleBox v-if="data" :data="data"/>
    <div v-else>
        Warehouse Showcase
    </div>
</template>


