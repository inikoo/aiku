<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
import { Popover, PopoverButton, PopoverPanel, Switch } from '@headlessui/vue'
import { ref } from 'vue'
import { faBorderTop, faBorderLeft, faBorderBottom, faBorderRight, faBorderOuter } from "@fad"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLink, faUnlink } from "@fal"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(faBorderTop, faBorderLeft, faBorderBottom, faBorderRight, faBorderOuter, faLink, faUnlink)

const model = defineModel()
const props = defineProps<{
    label: string
    scope: string
}>()


const arePaddingValuesSame = (padding) => {
    if (!padding) return false
    const values = Object.values(padding)
        .map(item => item.value) // Extract the value properties
        .filter(value => value !== undefined); // Filter out undefined values

    // Check if all values are the same
    return values.every(value => value === values[0]);
}
const isPaddingUnitLinked = ref(arePaddingValuesSame(model.value?.[props.scope]))
const changePaddingToSameValue = (newVal: number) => {
    for (let key in model.value[props.scope]) {
        if (model.value[props.scope][key].hasOwnProperty('value')) {
            model.value[props.scope][key].value = newVal; // Set value to 99
        }
    }
}
</script>

<template>
    <div class="flex flex-col bg-gray-100 shadow-md pt-1 pb-3 border-t border-gray-300">
        <div class="w-full text-center py-1 font-semibold select-none">{{ label }}</div>

        <div class="pb-2">
            <div class="px-3 flex justify-between items-center mb-2">
                <div class="text-xs">{{ trans('Unit') }}</div>
                <Popover v-slot="{ open }" class="relative">
                    <PopoverButton
                        :class="open ? 'text-indigo-500' : ''"
                        class="underline"
                    >
                        {{ model[props.scope].unit }}
                    </PopoverButton>

                    <transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="translate-y-1 opacity-0"
                        enter-to-class="translate-y-0 opacity-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="translate-y-0 opacity-100"
                        leave-to-class="translate-y-1 opacity-0"
                    >
                        <PopoverPanel v-slot="{ close }" class="bg-white shadow mt-3 absolute top-full right-0 z-10 w-32 transform rounded overflow-hidden">
                            <div @click="() => {model[props.scope].unit = 'px', close()}" class="px-4 py-1.5 cursor-pointer" :class="model[props.scope].unit == 'px' ? 'bg-indigo-500 text-white' : 'hover:bg-indigo-100'">px</div>
                            <div @click="() => {model[props.scope].unit = '%', close()}" class="px-4 py-1.5 cursor-pointer" :class="model[props.scope].unit == '%' ? 'bg-indigo-500 text-white' : 'hover:bg-indigo-100'">%</div>
                        </PopoverPanel>
                    </transition>
                </Popover>
            </div>

            <!-- Toggle: custom -->
            <div class="px-3 flex justify-between items-center mb-2">
                <div class="text-xs">{{ trans('Same value') }}</div>
                <Switch
                    v-model="isPaddingUnitLinked"
                    :class="[
                        isPaddingUnitLinked ? 'bg-slate-600' : 'bg-slate-300'
                    ]"
                    class="pr-1 relative inline-flex h-3 w-6 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75"
                >
                    <!-- <span class="sr-only">Use setting</span> -->
                    <span aria-hidden="true" :class="isPaddingUnitLinked ? 'translate-x-3' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-full w-1/2 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out" />
                </Switch>
            </div>

            <div class="pl-2 pr-4 flex items-center relative">
                <div class="relative">
                    <Transition name="slide-to-up">
                        <div v-if="isPaddingUnitLinked">
                            <div class="grid grid-cols-5 items-center">
                                <FontAwesomeIcon icon='fad fa-border-outer' v-tooltip="trans('Padding all')" class='' fixed-width aria-hidden='true' />
                                <div class="col-span-4">
                                    <PureInputNumber v-model="model[props.scope].top.value" @update:modelValue="(newVal) => isPaddingUnitLinked ? changePaddingToSameValue(newVal) : false" class="" :suffix="model[props.scope].unit" />
                                </div>
                            </div>
                        </div>

                        <div v-else class="space-y-2">
                            <div class="grid grid-cols-5 items-center">
                                <FontAwesomeIcon icon='fad fa-border-top' v-tooltip="trans('Padding top')" class='' fixed-width aria-hidden='true' />
                                <div class="col-span-4">
                                    <PureInputNumber v-model="model[props.scope].top.value" class="" :suffix="model[props.scope].unit" />
                                </div>
                            </div>
                            <div class="grid grid-cols-5 items-center">
                                <FontAwesomeIcon icon='fad fa-border-bottom' v-tooltip="trans('Padding bottom')" class='' fixed-width aria-hidden='true' />
                                <div class="col-span-4">
                                    <PureInputNumber v-model="model[props.scope].bottom.value" class="" :suffix="model[props.scope].unit" />
                                </div>
                            </div>
                            <div class="grid grid-cols-5 items-center">
                                <FontAwesomeIcon icon='fad fa-border-left' v-tooltip="trans('Padding left')" class='' fixed-width aria-hidden='true' />
                                <div class="col-span-4">
                                    <PureInputNumber v-model="model[props.scope].left.value" class="" :suffix="model[props.scope].unit" />
                                </div>
                            </div>
                            <div class="grid grid-cols-5 items-center">
                                <FontAwesomeIcon icon='fad fa-border-right' v-tooltip="trans('Padding right')" class='' fixed-width aria-hidden='true' />
                                <div class="col-span-4">
                                    <PureInputNumber v-model="model[props.scope].right.value" class="" :suffix="model[props.scope].unit" />
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </div>
</template>