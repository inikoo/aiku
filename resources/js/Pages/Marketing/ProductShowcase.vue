<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

const props = defineProps<{
    data: object
}>()
const product=props.data.data;

</script>


<template>

    <div class="lg:col-span-4 lg:row-end-1">
        <div class="aspect-h-3 aspect-w-4 overflow-hidden rounded-lg bg-gray-100">
            <img
                :src="route('media.show',product.image_id)"
                alt="product main image" class="object-cover object-center" />
        </div>
    </div>

</template>
