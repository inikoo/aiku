<script setup lang='ts'>
/**
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created on: 30-09-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

const props = defineProps<{
    data: {}
}>()
</script>

<template>
    <div>
        <pre>{{ data }}</pre>
    </div>
</template>