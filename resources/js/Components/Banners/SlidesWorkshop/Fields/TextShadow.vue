<script setup lang="ts">
import { faUpload } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
// import Input from '@/Components/Forms/Fields/Input.vue';
library.add(faUpload)

const props = defineProps<{
    data: any
    fieldName: string
    fieldData: any
    bannerType: string
}>()

console.log(props)
</script>

<template>
    <div class="block w-full border p-2">
        <input />
    </div>
</template>
