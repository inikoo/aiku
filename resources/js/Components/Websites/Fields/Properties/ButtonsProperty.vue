<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
import { Popover, PopoverButton, PopoverPanel, Switch } from '@headlessui/vue'
import { ref } from 'vue'
import { faBorderTop, faBorderLeft, faBorderBottom, faBorderRight, faBorderOuter } from "@fad"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLink, faUnlink } from "@fal"
import { faExclamation } from "@fas"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import ColorPicker from '@/Components/Utils/ColorPicker.vue'
library.add(faExclamation, faBorderTop, faBorderLeft, faBorderBottom, faBorderRight, faBorderOuter, faLink, faUnlink)

interface Borderproperty {
    color: string
    unit: string
    rounded: {
        unit: string
        topright: {
            value: number
        }
        topleft: {
            value: number
        }
        bottomright: {
            value: number
        }
        bottomleft: {
            value: number
        }
    }
    top: {
        value: number
    }
    right: {
        value: number
    }
    left: {
        value: number
    }
    bottom: {
        value: number
    }
}

const model = defineModel<Borderproperty>({
    required: true
})

const isBorderSameValue = ref(false)

</script>


<template>
    <!-- <pre>{{ model }}</pre> -->
    <div class="flex flex-col pt-1 pb-3">

        <div class="pb-2">
            <!-- Toggle: Border same value -->
            <div class="px-3 flex justify-between items-center mb-2">
                <div class="text-xs">{{ trans('Border same value') }}</div>
                <Switch v-model="isBorderSameValue" :class="[
                    isBorderSameValue ? 'bg-slate-600' : 'bg-slate-300'
                ]"
                    class="pr-1 relative inline-flex h-3 w-6 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
                    <!-- <span class="sr-only">Use setting</span> -->
                    <span aria-hidden="true" :class="isBorderSameValue ? 'translate-x-3' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-full w-1/2 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out" />
                </Switch>
            </div>
            <div class="pl-2 pr-4 flex items-center relative mb-3">
                <Transition name="slide-to-up">
                    <div v-if="isBorderSameValue">
                        <div class="grid grid-cols-5 items-center">
                            <FontAwesomeIcon icon='fad fa-border-outer' v-tooltip="trans('Padding all')" class='' fixed-width aria-hidden='true' />
                            <div class="col-span-4">
                                <PureInputNumber v-model="model.top.value" @update:modelValue="(newVal) => isBorderSameValue ? changeBorderValueToSame(newVal) : false" class="" suffix="px" />
                            </div>
                        </div>
                    </div>

                    <div v-else class="space-y-2">
                        <div class="grid grid-cols-5 items-center justify-center">
                            <FontAwesomeIcon icon='fad fa-border-top' v-tooltip="trans('Border top')" class='mx-auto' fixed-width aria-hidden='true' />
                            <div class="col-span-4">
                                <PureInputNumber v-model="model.top.value" class="" suffix="px" />
                            </div>
                        </div>
                        <div class="grid grid-cols-5 items-center">
                            <FontAwesomeIcon icon='fad fa-border-bottom' v-tooltip="trans('Border bottom')" class='mx-auto' fixed-width aria-hidden='true' />
                            <div class="col-span-4">
                                <PureInputNumber v-model="model.right.value" class="" suffix="px" />
                            </div>
                        </div>
                        <div class="grid grid-cols-5 items-center">
                            <FontAwesomeIcon icon='fad fa-border-left' v-tooltip="trans('Border left')" class='mx-auto' fixed-width aria-hidden='true' />
                            <div class="col-span-4">
                                <PureInputNumber v-model="model.bottom.value" class="" suffix="px" />
                            </div>
                        </div>
                        <div class="grid grid-cols-5 items-center">
                            <FontAwesomeIcon icon='fad fa-border-right' v-tooltip="trans('Border right')" class='mx-auto' fixed-width aria-hidden='true' />
                            <div class="col-span-4">
                                <PureInputNumber v-model="model.left.value" class="" suffix="px" />
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </div>
</template>