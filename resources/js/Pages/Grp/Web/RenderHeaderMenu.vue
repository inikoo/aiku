<script setup lang='ts'>
import NavigationMenu from './MenuRender.vue'
import { getComponent } from '@/Composables/getWorkshopComponents'
import { getComponent as getIrisComponent } from '@/Composables/getIrisComponents';

const props = defineProps<{
    data: {
        topBar: {
            id: number
            code: string
            data: {
                component: string
                fieldValue: {
                    key: string
                    greeting: {
                        text: string
                        visible: string
                    }
                    container: {
                        properties: {
                            text: {
                                color: string
                                fontFamily: string
                            }
                            background: {
                                type: string
                                color: string
                                image: {
                                    original: string | null
                                }
                            }
                        }
                    }
                    main_title: {
                        text: string
                        visible: string
                    }
                }
            }
            icon: string | null
            name: string
            show: boolean
            scope: string
            blueprint: {
                key: string[]
                name: string
                type: string[]
                props_data: any[]
                replaceForm?: {
                    key: string[]
                    type: string[]
                }[]
            }[]
            component: string
            created_at: string
            screenshot: string | null
            updated_at: string
            visibility: {
                in: boolean
                out: boolean
            }
            description: string | null
        }
    }
    menu: {
        key: string,
        data: object,
    }
    colorThemed: {
        color: string[]
    }
    loginMode:Boolean
    previewMode:Boolean
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()

</script>

<template>
    <div>
        <!-- Section: TopBars -->
         <div class="hidden lg:block">
            <component
                v-if="data?.topBar?.data?.fieldValue"
                :is="previewMode ? getIrisComponent(data?.topBar.code) : getComponent(data?.topBar.code)"
                v-model="data.topBar.data.fieldValue"
                :loginMode="loginMode"
                :colorThemed="colorThemed"
                @update:model-value="(e)=>emits('update:modelValue', e)"
            />
         </div>
        


        <!-- Section: Header -->
        <component
            v-if="data?.header?.code"
            :is="previewMode ? getIrisComponent(data?.header?.code) : getComponent(data?.header?.code)"
            v-model="data.header.data.fieldValue"
            :loginMode="loginMode"
            :colorThemed="colorThemed"
            @update:model-value="(e)=>emits('update:modelValue', e)"
        />


        <!-- Section: Menu -->
        <NavigationMenu 
            v-if="menu" 
            :data="menu?.menu" 
            :colorThemed="colorThemed" 
            class="hidden md:block" 
        />
    </div>
</template>