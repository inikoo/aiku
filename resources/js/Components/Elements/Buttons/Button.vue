<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sun, 30 Oct 2022 15:27:23 Greenwich Mean Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { trans } from 'laravel-vue-i18n'
import { faSave as fadSave } from '@fad'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faDownload } from '@fal'
import { faArrowLeft, faPencil, faTrashAlt } from '@far'
import { faPlus, faSave, faUpload } from '@fas'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { useLayoutStore } from '@/Stores/layout'
import { faSpinnerThird } from '@fad'

const layout = useLayoutStore()

const props = withDefaults(defineProps<{
    style?: string
    size?: string
    icon?: string | string[]
    iconRight?: string | string[]
    action?: string
    label?: string
    full?: boolean
    capitalize?: boolean
    tooltip?: string
    loading?:boolean
}>(), {
    style: 'primary',
    size: 'm',
    capitalize: true,
    loading:false
})

library.add(faPlus, faSave, fadSave, faUpload, faDownload, faArrowLeft, faPencil, faTrashAlt, faSpinnerThird)

let styleClass = ''
let sizeClass = ''

// Styling the Button depends on the 'style' props
if (props.style == 'primary' || props.style == 'create' || props.style == 'save' || props.style == 'upload') {
    styleClass = 'border border-indigo-500 bg-indigo-600 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white hover:bg-none'
}

else if (props.style == 'secondary' || props.style == 'edit') {
    styleClass = 'border border-indigo-500 text-gray-600 hover:bg-indigo-500/30 focus:bg-indigo-500 focus:text-white'
}

else if (props.style == 'tertiary') styleClass = 'bg-transparent border border-gray-300 text-gray-700 hover:bg-gray-200/70'
else if (props.style == 'rainbow') styleClass = 'bg-indigo-500 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'

else if (props.style == 'delete' || props.style == 'negative' || props.style == 'cancel') styleClass = 'border border-red-400 text-red-500 hover:text-red-800 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2'
else if (props.style == 'positive') styleClass = 'border border-lime-500 text-lime-600 hover:text-lime-800 hover:bg-lime-50 focus:outline-none focus:ring-2 focus:ring-lime-600 focus:ring-offset-2'

else if (props.style == 'white') styleClass = 'bg-white text-gray-600'
else if (props.style == 'red') styleClass = 'bg-red-500 hover:bg-red-600 text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2'
else if (props.style == 'green') styleClass = 'bg-lime-400 hover:bg-lime-500 text-white focus:outline-none focus:ring-2 focus:ring-lime-400 focus:ring-offset-2'
else if (props.style == 'gray') styleClass = 'bg-gray-200 hover:bg-gray-300 border border-gray-400 text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2'

// else if (props.style == 'negative' || props.style == 'cancel') styleClass = 'border border-red-400 text-red-600 hover:text-red-800 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2'
else if (props.style == 'disabled') styleClass = 'cursor-not-allowed border border-gray-300 bg-transparent text-gray-700 hover:bg-gray-200/70 disabled:cursor-not-allowed disabled:opacity-70'
else styleClass = 'border border-gray-300 bg-transparent text-gray-700 hover:bg-gray-200/70'

// Styling depends on the 'size' props
switch (props.size) {
    case 'xxs':
        sizeClass = 'rounded px-2 py-1 text-xxs'
        break
    case 'xs':
        sizeClass = 'rounded px-2.5 py-1.5 text-xs'
        break
    case 's':
        sizeClass = 'rounded-md px-3 py-[7px] text-sm'
        break
    case 'm':
        sizeClass = 'rounded-md px-4 py-[9px] text-sm'
        break
    case 'l':
        sizeClass = 'rounded-md px-[18px] py-[11px] text-base'
        break
    case 'xl':
        sizeClass = 'rounded-md px-6 py-[13px] text-lg'
        break
}

// Auto add label for several conditions
const getActionLabel = (label: string | undefined) => {
    if (label) {
        return trans(label)
    } else {
        switch (props.style) {
            case "edit":
                return trans("edit")
            case "save":
                return trans("save")
            case "create":
                return trans("create")
            case "exit":
                return trans("exit")
            case "cancel":
                return trans("cancel")
            case "delete":
                return trans("delete")
            case "clearMulti":
                return trans("clear")
            default:
                return ""
        }
    }
}

// Auto add icon for several conditions
const getActionIcon = (icon: any) => {
    if (icon) {
        return icon
    } else {
        switch (props.style) {
            case "edit":
                return ["far", "fa-pencil"]
            case "save":
                return ["fas", "fa-save"]
            case "cancel":
            case "exit":
                return ["far", "fa-arrow-left"]
            case "create":
                return ["fas", "fa-plus"]
            case "delete":
                return ["far", "fa-trash-alt"]
            case "withMulti":
                return ["far", "fa-border-all"]
            case "upload":
                return ["fas", "fa-upload"]
            default:
                return null
        }
    }
}

</script>

<template>
    <button type="button"
        class="leading-4 inline-flex items-center gap-x-2 font-medium shadow-sm focus:outline-none"
        :class="[
            // icon ? 'px-2 sm:px-4' : 'px-3 sm:px-5 ',
            full ? 'w-full justify-center' : 'min-w-max',
            styleClass,
            sizeClass
        ]"
        :style="{
            backgroundColor: layout?.app?.theme[4],
            color: layout?.app?.theme[5]
        }"
        :disabled="style == 'disabled'"
        v-tooltip="tooltip ?? undefined"    
    >
        <slot>
            <slot name="loading">
                <FontAwesomeIcon v-if="loading" icon='fad fa-spinner-third' class='animate-spin' fixed-width  aria-hidden="true"/>
            </slot>
            <slot name="icon" v-if="!loading">
                <FontAwesomeIcon v-if="getActionIcon(icon)" :icon="getActionIcon(icon)" fixed-width class="" aria-hidden="true"/>
            </slot>
            <span v-if="getActionLabel(label)" class="leading-none" :class="{'capitalize': capitalize}">{{ getActionLabel(label) }}</span>
            <slot name="iconRight">
                <FontAwesomeIcon v-if="iconRight" :icon="getActionIcon(iconRight)" fixed-width class="" aria-hidden="true"/>
            </slot>
        </slot>
    </button>
</template>
