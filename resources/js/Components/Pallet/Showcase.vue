<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Timeline from '@/Components/Timeline/Timeline.vue'

const props = defineProps<{
    data: object
}>()

const options =
    [
        {
            label: "Mailshot created",
            timestamp: "2024-01-30T07:59:12.000000Z"
        },
        {
            label: "Mailshot composed",
            timestamp: "2024-01-30T07:59:24.000000Z"
        },
        {
            label: "Start send",
            timestamp: "2024-01-30T07:59:39.000000Z"
        },
        {
            label: "Sent",
            timestamp: null
        }
    ]


</script>
  
<template>
    <Timeline :options="options" />
</template>
  