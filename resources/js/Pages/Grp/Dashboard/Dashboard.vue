<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 24 Jun 2023 11:02:25 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { useLayoutStore } from '@/Stores/layout'

</script>

<template layout="App">
    <div>
        resources/js/Pages/Grp/Dashboard/Dashboard.vue
        <pre>{{ route().v()?.params?.organisation }}</pre>
        <pre>{{ usePage() }}</pre>
    </div>
</template>
