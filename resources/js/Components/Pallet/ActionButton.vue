<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:32:23 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faTrashAlt } from "@far";
import { faSignOutAlt, faSpellCheck, faCheck, faTimes, faCheckDouble, faCross, faFragile, faGhost, faBoxUp, faStickyNote, faSquare } from "@fal";
import Button from "@/Components/Elements/Buttons/Button.vue";
import { inject, ref } from "vue";
import Popover from "@/Components/Popover.vue";
import { trans } from "laravel-vue-i18n"

library.add(faTrashAlt, faSignOutAlt, faSpellCheck, faCheck, faTimes, faCheckDouble, faCross, faFragile, faGhost, faBoxUp, faStickyNote, faSquare);

const props = defineProps<{
    item: Object
}>();


const loading = ref(false)
const status = ref('damaged')
const form = useForm({ message: '' })

/* const onUpdateStatus = (routes, data, type) => {
    router.patch(route(routes.name, routes.parameters), data, {
        onStart: () => {
            if (type == 'damaged') loadingDamaged.value = true
            if (type == 'lost') loadingLost.value = true
        },
        onFinish: () => {
            if (type == 'damaged') loadingDamaged.value = false
            if (type == 'lost') loadingLost.value = false
        }
    })
}
 */


 const SendData=(routes,close)=>{
    form.patch(route(
        routes.name,
        routes.parameters
    ), {
        preserveScroll: true,
        onStart:()=>{loading.value = true},
        onSuccess: () => {
            close()
            loading.value = false
        },
        onError: (errors) => {
            loading.value = false
        },
        onFinish: ()=>{loading.value = false}
    })
}


const typePallet = [
    { label: 'Damaged', value: 'damaged' },
    { label: 'Lost', value: 'lost' },
]
</script>

<template>
    <Popover v-if="item.status === 'storing'" width="w-full" class="relative">
        <template #button>
            <Button :key="item.index" iconRight="fal fa-fragile" :size="'xs'" type="negative" />
        </template>
        <template #content="{ close }">
            <div class="w-[250px]">

                <span class="text-xs  my-2">{{ trans('Status') }}: </span>
                <div class="flex items-center">
                    <div v-for="(typeData, typeIdx) in typePallet" :key="typeIdx" class="relative py-3 mr-4">
                        <div>
                            <input type="checkbox" :id="typeData.value" :value="typeData.value"
                                :checked="status == typeData.value" @input="()=>status = typeData.value"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                            <label :for="typeData.value" class="ml-2">{{ typeData.label }}</label>
                        </div>
                    </div>
                </div>


                <label for="message]" class="text-xs  my-2">{{ trans('Message') }}:</label>
                <div class="rounded-md shadow-sm">
                    <textarea v-model.trim="form.message" id="message" name="message" placeholder="message..." rows="3"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
            </div>

            <div class="flex justify-end mt-3">
                <Button :style="'save'" :loading="loading" @click="SendData( status == 'damaged' ? item.setAsDamaged : item.setAsLost, close)" />
            </div>
        </template>
    </Popover>
</template>




<style lang="scss"></style>