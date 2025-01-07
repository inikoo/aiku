<script setup lang='ts'>
import { getIrisComponent } from '@/Composables/getIrisComponents'
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
    <!-- Section: Header-Topbar -->
    <component v-if="data?.topBar?.data.fieldValue"
        :is="getIrisComponent(data?.topBar.code)"
        :fieldValue="data.topBar.data.fieldValue"
        v-model="data.topBar.data.fieldValue"
        :loginMode="true"
        :previewMode="true"
        :uploadImageRoute="null"
        :colorThemed="colorThemed" @update:model-value="(e) => emits('update:modelValue', e)"
    />

    <!-- Section: Header-Menu -->
    <component
        :is="getIrisComponent(data?.header?.code)"
        :fieldValue="data.header.data.fieldValue"
        :loginMode="true"
        :previewMode="true"
        :colorThemed="colorThemed"
    />

        <NavigationMenu 
            :data="menu" 
            :colorThemed="colorThemed" 
            class="hidden md:block" 
        />
</template>