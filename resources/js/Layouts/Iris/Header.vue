<script setup lang='ts'>
// import HeaderTypeA1 from '@/Components/Iris/Blocks/Header/HeaderTypeA1.vue'
import { getComponent } from '@/Components/Websites/Header/Content'
import { usePage } from '@inertiajs/vue3'

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

// const componentList = (componentName: string) => {
//     const components: any = {
//         'HeaderTypeA1': HeaderTypeA1,
//     }

//     return components[componentName]
// }


// console.log('props Header.vue', props.data)
</script>

<template>
    <!-- <pre>{{ usePage().props.iris_header }}</pre> -->
    <div>
        <component :is="getComponent(usePage().props.iris_header.component)"
            :modelValue="usePage().props.iris_header"
        />
    </div>

</template>