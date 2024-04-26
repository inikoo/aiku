<script setup lang='ts'>

import HeaderTypeA1 from '@/Components/Iris/Blocks/Header/HeaderTypeA1.vue'

const props = defineProps<{
    data: {
        header_name: string
        header_data: {
            name: string
                data: {
                imgLogo?: string
                title?: string
                description?: string
            }
        }[]
    }
}>()

const componentList = (componentName: string) => {
    const components: any = {
        'HeaderTypeA1': HeaderTypeA1,
    }

    return components[componentName]
}


console.log('props Header.vue', props.data)
</script>

<template>
    <div>
        <component :is="componentList(data.header_name)" :data="data.header_data" />
    </div>

</template>