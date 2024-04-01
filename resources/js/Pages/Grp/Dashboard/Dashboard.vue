<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 24 Jun 2023 11:02:25 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { useLayoutStore } from '@/Stores/layout'

import Layout from '@/Layouts/Grp.vue'

defineOptions({ layout: Layout })

</script>

<template>
    <div>
        <!-- <pre>{{ route().v()?.params?.organisation }}</pre> -->
        <!-- <pre>{{ usePage().props }}</pre> -->
        <!-- <pre>{{ route(useLayoutStore().currentRoute, useLayoutStore().currentParams) }}</pre> -->
        <!-- <pre>{{ useLayoutStore().navigation.org }}</pre> -->
    </div>
</template>
