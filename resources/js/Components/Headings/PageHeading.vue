<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Fri, 07 Oct 2022 09:34:00 Central European Summer Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"

import { library } from "@fortawesome/fontawesome-svg-core"
import { faMapSigns, faPallet, faTruckCouch, faUpload, faWarehouse, faEmptySet} from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import MetaLabel from "@/Components/Headings/MetaLabel.vue"
import Container from "@/Components/Headings/Container.vue"
import Action from "@/Components/Forms/Fields/Action.vue"
import SubNavigation from "@//Components/Navigation/SubNavigation.vue"
import { kebabCase } from "lodash"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { faNarwhal } from "@fas"
import { faLayerPlus } from "@far"
import { PageHeading as PageHeadingTypes } from '@/types/PageHeading'
import { ref } from "vue"

library.add(faTruckCouch, faUpload, faMapSigns, faNarwhal, faLayerPlus, faPallet, faWarehouse, faEmptySet)

const props = defineProps<{
    data: PageHeadingTypes
    dataToSubmit?: any
    dataToSubmitIsDirty?: any
}>()

const isButtonLoading = ref<boolean | string>(false)

if (props.dataToSubmit && props.data.actionActualMethod) {
    props.dataToSubmit["_method"] = props.data.actionActualMethod
}

const originUrl = location.origin
</script>


<template>
    <!-- Sub Navigation -->
    <SubNavigation v-if="data.subNavigation?.length" :dataNavigation="data.subNavigation" />
    
    <div class="mx-4 pt-2 pb-4 sm:py-4 md:pb-2 md:pt-3 lg:py-2 grid grid-flow-col justify-between items-center">
        <div class="">

            <!-- Section: Main Title -->
            <div class="flex leading-none py-1 items-center gap-x-2 font-bold text-gray-700 text-2xl tracking-tight ">
                <div v-if="data.container" class="text-slate-500 text-lg">
                    <Link v-if="data.container.href"
                        :href="route(data.container.href['name'], data.container.href['parameters'])"
                    >
                        <Container :data="data.container" />
                    </Link>
                    <div v-else class="flex items-center gap-x-1">
                        <Container :data="data.container" />
                    </div>
                </div>
                <div v-if="data.icon" class="inline text-gray-400">
                    <FontAwesomeIcon
                        v-tooltip="data.icon.tooltip || ''"
                        aria-hidden="true"
                        :icon="data.icon.icon || data.icon"
                        size="sm"
                        fixed-width />
                </div>
                
                <div class="flex flex-col sm:flex-row gap-y-1.5 gap-x-3">
                    <h2 :class="data.noCapitalise ? '' : 'capitalize'" class="">
                        <span v-if="data.model" class="text-gray-400 mr-2 font-medium block sm:inline">{{ data.model }}</span>
                        <span class="mt-1 sm:mt-0 inline-block">{{ data.title }}</span>
                    </h2>
                    
                    <!-- Section: After Title -->
                    <slot name="afterTitle">
                        <div class="flex gap-x-2 items-center">
                            <FontAwesomeIcon v-if="data.iconRight"
                                v-tooltip="data.iconRight.tooltip || ''"
                                :icon="data.iconRight.icon" class="h-4" :class="data.iconRight.class"
                                aria-hidden="true"
                            />
                            <div v-if="data.afterTitle" class="text-gray-400 font-normal text-lg leading-none">
                                {{ data.afterTitle.label }}
                            </div>
                        </div>
                    </slot>
                </div>
            </div>

            <!-- Section: mini Tabs -->
            <div v-if="data.meta" class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:gap-x-6 sm:gap-y-0.5 text-gray-500 text-xs pt-2">
                <div v-for="item in data.meta" class="flex items-center">
                    <slot :name="`tabs-${item.key}`" :data="item">
                        <FontAwesomeIcon v-if="item.leftIcon"
                            :title="item.leftIcon.tooltip"
                            fixed-width
                            aria-hidden="true" :icon="item.leftIcon.icon" class="text-gray-400 pr-0.5" />
                        <component :is="item.href?.name ? Link : 'div'" :href="item.href?.name ? route(item.href.name, item.href.parameters) : '#'"
                            :class="[
                                item.href?.name
                                ? $page.url.startsWith((route(item.href.name, item.href.parameters)).replace(new RegExp(originUrl, 'g'), ''))
                                    ? 'text-gray-600 font-medium'
                                    : 'underline text-gray-400 hover:text-gray-500'
                                : 'text-gray-400'
                            ]"
                        >
                            <MetaLabel :item="item" />
                        </component>
                    </slot>
                </div>
            </div>
        </div>

        <!-- Section: Button and/or ButtonGroup -->
        <slot name="button" :dataPageHead="{ ...props }">
            <div class="flex flex-col items-end sm:flex-row sm:items-center gap-2 rounded-md">
                <template v-for="(action, actIndex) in data.actions">
                    <template v-if="action">
                        <!-- Button -->
                        <slot v-if="action.type == 'button'" :name="`button-${kebabCase(action.label)}`" :action="action">
                            <slot :name="`button-index-${actIndex}`" :action="action">
                                <Action v-if="action" :action="action" :dataToSubmit="dataToSubmit" />
                            </slot>
                        </slot>
                        
                        <!-- ButtonGroup -->
                        <slot v-if="action.type == 'buttonGroup'" :name="`button-group-${action.key}`" :action="action">
                            <div class="rounded-md flex" :class="[(action.button?.length || 0) > 1 ? 'shadow' : '']">
                                <template v-if="action.button?.length">
                                    <slot v-for="(button, index) in action.button" :name="'button-group-' + kebabCase(button.label)" :action="button">
                                        <component :is="button.route?.name ? Link : 'div'"
                                            :href="button.route?.name ? route(button.route.name, button.route.parameters) : '#'" class=""
                                            :method="button.route?.method || 'get'"
                                            @start="() => isButtonLoading = 'buttonGroup' + index"
                                            @error="() => isButtonLoading = false"
                                            :as="button.target ? 'a' : 'div'"
                                            :target="button.target"
                                        >
                                            <Button
                                                :style="button.style"
                                                :label="button.label"
                                                :icon="button.icon"
                                                :loading="isButtonLoading === 'buttonGroup' + index"
                                                :iconRight="button.iconRight"
                                                :disabled="button.disabled"
                                                :key="`ActionButton${button.label}${button.style}`" :tooltip="button.tooltip"
                                                class="inline-flex items-center h-full rounded-none text-sm border-none font-medium shadow-sm focus:ring-transparent focus:ring-offset-transparent focus:ring-0"
                                                :class="{'rounded-l-md': index === 0, 'rounded-r-md ': index === action.button?.length - 1}"
                                            >
                                            </Button>
                                        </component>
                                    </slot>
                                </template>
                            </div>
                        </slot>
                    </template>
                </template>
                <slot name="other" :dataPageHead="{ ...props }" />
            </div>
        </slot>

    </div>
    <hr class="border-gray-300" />
</template>
