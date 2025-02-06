<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 23 May 2024 09:45:43 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from "@fortawesome/fontawesome-svg-core"
import { faRobot, faPlus, faMinus, faUndoAlt } from "@far"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Button from "@/Components/Elements/Buttons/Button.vue"
import InputNumber from "primevue/inputnumber"
import { ref } from "vue"
import { faSave as fadSave } from "@fad"
import { faSave as falSave, faInfoCircle } from "@fal"
import { faAsterisk, faQuestion, faSpinner } from "@fas"
import { useForm } from "@inertiajs/vue3"
import LoadingIcon from "./Utils/LoadingIcon.vue"
import { routeType } from "@/types/route"
import { trans } from "laravel-vue-i18n"

library.add( faRobot, faPlus, faMinus, faUndoAlt, faAsterisk, faQuestion, falSave, faInfoCircle, fadSave, faSpinner )

const props = defineProps<{
    modelValue: number
    min?: number
    max?: number
    saveOnForm?: boolean
    routeSubmit?: routeType
    keySubmit?: string
    bindToTarget?: {}
}>()

const emits = defineEmits<{
    (e: 'onSave', value: string | number): void
}>()


const form = useForm({
    quantity: props.modelValue,
})

const onSaveViaForm = () => {
    if(!props.routeSubmit?.name) return
    
    form
    .transform((data) => ({
        [props.keySubmit || 'quantity']: data.quantity
    }))
    .submit(
        props.routeSubmit?.method || 'post',
        route(props.routeSubmit?.name, props.routeSubmit?.parameters),
        {
            preserveScroll: true,
        }
    )
}

const keyIconUndo = ref(0)

</script>

<template>
    <div class="relative w-full">
        <div class="flex items-center justify-center border border-gray-300 rounded gap-y-1 px-1">
            <!-- Button: Save -->
            <button
                @click="() => (keyIconUndo++, form.reset('quantity'))"
                v-tooltip="trans('Reset value')"
                class="relative flex items-center justify-center p-2"
                :class="form.isDirty ? 'cursor-pointer hover:text-gray-800 disabled:text-gray-400' : 'text-gray-400'"
                :disabled="form.processing || !form.isDirty"
                type="submit">
                <div class="text-base flex items-center rounded-full hover:bg-gray-200 cursor-pointer">
                    <Transition name="spin-to-left">
                        <FontAwesomeIcon :key="keyIconUndo" icon='far fa-undo-alt' class='' fixed-width aria-hidden='true' />
                    </Transition>
                </div>
            </button>

            <!-- Section: - and + -->
            <div class="transition-all relative inline-flex items-center justify-center" :class="bindToTarget?.fluid ? 'w-full' : 'w-28'">
                <!-- Button: Minus -->
                <div @click="form.quantity = form.quantity > 0 ? form.quantity - 1 : 1"
                    class="leading-4 cursor-pointer inline-flex items-center gap-x-2 font-medium focus:outline-none disabled:cursor-not-allowed min-w-max bg-transparent border border-gray-300 text-gray-700 hover:bg-gray-200/70 disabled:bg-gray-200/70 rounded px-1 py-1.5 text-xs justify-self-center">
                    <FontAwesomeIcon icon="fas fa-minus" fixed-width aria-hidden="true" />
                </div>

                <!-- Input -->
                <div
                    class="text-center tabular-nums border border-transparent hover:border-dashed hover:border-gray-300 group-focus:border-dashed group-focus:border-gray-300">
                    <InputNumber 
                        v-model="form.quantity" 
                        @update:model-value="(e)=>form.quantity=e"
                        buttonLayout="horizontal" 
                        :min="min || 0"
                        :max="max || undefined"
                        style="width: 100%" 
                        :inputStyle="{
                            padding: '0px',
                            width: bindToTarget?.fluid ? undefined : '50px',
                            color: 'gray',
                            border: 'none',
                            textAlign: 'center',
                        }"
                        v-bind="bindToTarget"
                    />
                </div>
                <!-- Button: Plus -->
                <div @click="() => form.quantity++"
                    class="leading-4 cursor-pointer inline-flex items-center gap-x-2 font-medium focus:outline-none disabled:cursor-not-allowed min-w-max bg-transparent border border-gray-300 text-gray-700 hover:bg-gray-200/70 disabled:bg-gray-200/70 rounded px-1 py-1.5 text-xs justify-self-center">
                    <FontAwesomeIcon icon="fas fa-plus" fixed-width aria-hidden="true" />
                </div>
            </div>

            <!-- Button: Save -->
            <button class="relative flex items-center justify-center p-2"
                :class="{ 'text-gray-400': !form.isDirty }"
                :disabled="form.processing || !form.isDirty" type="submit">
                <LoadingIcon v-if="form.processing" class="text-2xl" />
                <template v-else>
                    <FontAwesomeIcon v-if="form.isDirty" @click="saveOnForm ? onSaveViaForm() : emits('onSave', form)"
                        :style="{ '--fa-secondary-color': 'rgb(0, 255, 4)' }" icon="fad fa-save" fixed-width class=" cursor-pointer text-2xl"
                        aria-hidden="true" />
                    <FontAwesomeIcon v-else icon="fal fa-save" fixed-width class="text-2xl" aria-hidden="true" />
                </template>
            </button>
        </div>
    </div>
</template>

<style scoped>
::v-deep(.p-inputnumber) {
    border-bottom: 2px solid transparent;
    transition: border-color 0.3s;
}

::v-deep(.p-inputnumber:focus-within) {
    border-bottom: 2px solid #4b5563;
    /* gray-500 */
}
</style>
