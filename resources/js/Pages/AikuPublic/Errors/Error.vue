<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 26 Sept 2024 18:40:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">


const props = defineProps<{
  status: number,
  title: string,
  description: string,

}>();
console.log(props.status)
console.log(props.title)
console.log(props.description)

</script>

<template>
TodoL Her should be svg + props BE texts
</template>