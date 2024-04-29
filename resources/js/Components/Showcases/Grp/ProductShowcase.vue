<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'

const props = defineProps<{
    data: {}
}>()


</script>


<template>
    <div>
        resources/js/Components/Showcases/Grp/ProductShowcase.vue
        <pre>{{ usePage().props }}</pre>
    </div>

</template>
