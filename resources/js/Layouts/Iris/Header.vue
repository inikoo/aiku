<script setup lang='ts'>
import { getComponent } from "@/Composables/getIrisComponents"
import NavigationMenu from '@/Layouts/Iris/NavigationMenu.vue'
import { routeType } from "@/types/route"


const props = defineProps<{
    data: {
        key: string,
        data: object,
        blueprint: object
        loginRoute?: routeType
    }
    menu: {
        key: string,
        data: object,
        blueprint: object
    }
    colorThemed: object
}>()

</script>

<template>
    <component v-if="data?.topBar?.data.fieldValue" :is="getComponent(data?.topBar.code)"
        v-model="data.topBar.data.fieldValue" :loginMode="true" :previewMode="true" :uploadImageRoute="null"
        :colorThemed="colorThemed" @update:model-value="(e) => emits('update:modelValue', e)" />

    <component :is="getComponent(data?.header?.code)" v-model="data.header.data.fieldValue" :loginMode="true"
        :previewMode="true" :colorThemed="colorThemed" />

        <NavigationMenu 
            :data="menu" 
            :colorThemed="colorThemed" 
            class="hidden md:block" 
        />
</template>