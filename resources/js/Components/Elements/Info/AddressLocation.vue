<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 15 May 2023 13:12:47 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

const props = defineProps<{
    data: string[]
}>()

const countryCode=props.data[0]
const countryName=props.data[1]
const addressLocation=props.data[2]??''

const flag= '/flags/'+countryCode.toLowerCase()+'.png'  ;

</script>

<template>
    <img class="inline pr-1" :src='flag' :alt="countryCode"   :title='countryName'  /> {{addressLocation}}
</template>


