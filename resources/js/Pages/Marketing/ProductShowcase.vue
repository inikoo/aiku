<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">



const props = defineProps<{
    data: object
}>();
const product = props.data.data;


</script>


<template>

    <div class="grid grid-cols-3  ">
        <img
            :src="route('media.show',product.image_id)"
            alt="product main image" class="object-cover object-center ml-10"
        />
            <div class="grid text-center">
                <div>
                    hello
                </div>
                <div>
                    hello
                </div>
            </div>
            <div class="grid text-center" >
                <div>
                    hello
                </div>
                <div>
                    hello
                </div>
            </div>
    </div>



</template>
