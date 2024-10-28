<script setup lang='ts'>
import { getComponent as getComponentsHeader } from '@/Components/Websites/Header/Content'
import NavigationMenu from './MenuRender.vue'
import { routeType } from "@/types/route"
import IrisLoginInformation from '@/Layouts/Iris/IrisLoginInformation.vue'
import { getTopbarComponent } from '@/Components/Websites/Topbar/TopbarList'

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
        bluprint: object
    }
    colorThemed: {
        color: string[]
    }
    editDataTools : any
    loginMode:Boolean
}>()

console.log('inii',props)
</script>

<template>
    <div>
        <!-- <pre>{{ data.topBar.data }}</pre> -->
        <!-- Section: Topbar -->
        <component
            v-if="data?.topBar?.data.fieldValue"
            :is="getTopbarComponent(data?.topBar.code)"
            v-model="data.topBar.data.fieldValue"
            :loginMode="loginMode"
            :previewMode="editDataTools.previewMode"
            :uploadImageRoute="null"
            :colorThemed="colorThemed"
        />


        <!-- Section: Header -->
        <component
            v-if="data?.header?.code"
            :is="getComponentsHeader(data?.header?.code)"
            v-model="data.header.data.fieldValue"
            :loginMode="loginMode"
            :previewMode="editDataTools.previewMode"
            :uploadImageRoute="null"
            :colorThemed="colorThemed"
        />


        <!-- Section: Menu -->
        <NavigationMenu v-if="menu" :data="menu" :colorThemed="colorThemed" class="hidden md:block" />
    </div>
</template>