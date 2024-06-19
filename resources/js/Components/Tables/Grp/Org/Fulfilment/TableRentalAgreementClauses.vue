<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 19 Jun 2024 15:34:14 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from '@/Components/Table/Table.vue'
import { library } from "@fortawesome/fontawesome-svg-core"
import { faRobot } from '@fal'


library.add(faRobot)

defineProps<{
    data: object
    tab?: string,
}>()




</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">


    </Table>
</template>
