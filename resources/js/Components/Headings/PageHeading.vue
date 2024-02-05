<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Fri, 07 Oct 2022 09:34:00 Central European Summer Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"

import { library } from "@fortawesome/fontawesome-svg-core"
import { faTruckCouch } from '@fal'
import { } from '@far'
import { } from '@fas'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { capitalize } from "@/Composables/capitalize"
import MetaLabel from "@/Components/Headings/MetaLabel.vue"
import Container from "@/Components/Headings/Container.vue"
import Action from "@/Components/Forms/Fields/Action.vue"
import SubNavigation from "@//Components/Navigation/SubNavigation.vue"
import { Action as ActionTS } from "@/types/Action"
import { routeType } from '@/types/route'
import { kebabCase } from 'lodash'
import Button from "../Elements/Buttons/Button.vue"

interface Icon {
    icon: string[] | string
}

library.add(faTruckCouch)

const props = defineProps<{
    data: {
        title: string
        noCapitalise?:boolean
        icon: {
            icon: Icon
            tooltip: string
        }
        actionActualMethod?: string
        subNavigation?: any
        meta?: any
        actions?: ActionTS[]
        iconRight?: {
            title: string
            icon: string
            tooltip?: string
            class: string
        }
        container: {
            tooltip: any
            icon: Icon
            label: string
            href: routeType
        }
    },
    dataToSubmit?: any
    dataToSubmitIsDirty?: any
}>()


if (props.dataToSubmit && props.data.actionActualMethod) {
    props.dataToSubmit['_method'] = props.data.actionActualMethod
}

const originUrl = location.origin
</script>


<template>
    <div class="mx-4 py-4 md:pb-2 md:pt-3 lg:py-2 grid grid-flow-col justify-between items-center">
        <div>
            <!-- Sub Navigation -->
            <SubNavigation v-if="data.subNavigation" :dataNavigation="data.subNavigation" />

            <!-- Section: Main Title -->
            <div class="flex leading-none py-1 items-center gap-x-2 font-bold text-gray-700 text-2xl tracking-tight ">
                <div v-if="data.container" class="text-slate-500 text-lg">
                    <Link v-if="data.container.href"
                        :href="route(
                            data.container.href['name'],
                            data.container.href['parameters']
                    )">
                        <Container :data="data.container"/>
                    </Link>
                    <div v-else class="flex items-center gap-x-1">
                        <Container :data="data.container" />
                    </div>
                </div>
                <div v-if="data.icon" class="inline text-gray-400">
                    <FontAwesomeIcon :title="capitalize(data.icon.tooltip ?? '')" aria-hidden="true"
                        :icon="data.icon.icon" size="sm" class=""/>
                    <!-- <FontAwesomeIcon v-if="data.iconBis" :title="capitalize(data.iconBis.tooltip ?? '')" aria-hidden="true"
                        :icon="data.iconBis.icon" size="sm" class="" :class="data.iconBis.class"/> -->
                </div>
                <h2 :class="!data.noCapitalise? 'capitalize' : ''">{{ data.title }}</h2>
                <FontAwesomeIcon v-if="data.iconRight" :title="capitalize(data.iconRight.tooltip ?? '')" aria-hidden="true"
                    :icon="data.iconRight.icon" class="h-4" :class="data.iconRight.class"/>
            </div>

            <!-- Section: mini Tabs -->
            <div v-if="data.meta" class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6 text-gray-500 text-xs pt-2">
                    <div v-for="item in data.meta" class="flex items-center">
                        <FontAwesomeIcon v-if="item.leftIcon"
                            :title="capitalize(item.leftIcon.tooltip)"
                            aria-hidden="true" :icon="item.leftIcon.icon"  class="text-gray-400 pr-2"/>
                        <Link v-if="item.href" :href="`${route(item.href.name, item.href.parameters)}`"
                            :class="[
                                $page.url.startsWith((route(item.href.name, item.href.parameters)).replace(new RegExp(originUrl, 'g'), '')) ? 'text-org-600 font-medium' : 'text-org-300 hover:text-org-500'
                            ]"
                        >
                            <MetaLabel :item=item />
                        </Link>
                        <span v-else>
                            <MetaLabel :item=item />
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Button and/or ButtonGroup -->
        <slot name="button" :dataPageHead="{ ...props }">
            <div class="flex items-center gap-2">
                <div v-for="action in data.actions">
                    <slot v-if="action.type == 'button'" :name="`button-${kebabCase(action.label)}`" :action="{ action }">
                        <Action v-if="action" :action="action" :dataToSubmit="dataToSubmit"/>
                    </slot>
                </div>
                <slot name="other" :dataPageHead="{ ...props }"/>
            </div>
        </slot>
    </div>
    <hr class="border-gray-300"/>
</template>
