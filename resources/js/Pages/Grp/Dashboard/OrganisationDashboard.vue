

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 18 Jan 2024 15:36:09 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { useLayoutStore } from '@/Stores/layout'

const layout = useLayoutStore()

</script>

<template>
    <div>
        resources/js/Pages/Grp/Dashboard/OrganisationDashboard.vue
        <pre>{{ layout.navigation }}</pre>
    </div>
</template>
