<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 12 Sept 2022 15:58:38 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<template>
    <span v-for="(job_position, key) in job_positions" :key="key" :title="job_position.code" :class="[color(job_position.slug),'inline-flex items-center rounded-full  px-3 py-0.5 text-sm font-medium  mr-2']">{{ job_position.slug }}</span>
</template>


<script setup>
defineProps(['job_positions']);

const color = (position) => {
    switch (position) {
        case 'prod-w':
            return 'bg-pink-100 text-pink-800';

        default:
            return 'bg-gray-100 text-gray-800';
    }

};

</script>
