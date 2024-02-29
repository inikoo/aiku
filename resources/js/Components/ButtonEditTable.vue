<!--
  Author: Raul Perusquia <raul@inikoo.com>
  Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  Copyright (c) 2023, Raul A Perusquia Flores
-->

<script setup lang="ts">
import { defineProps, withDefaults, ref, defineEmits} from "vue"
import { useLayoutStore } from "@/Stores/retinaLayout"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"

const props = withDefaults(
    defineProps<{
        data: object
        routeName: string
        style?: string
        size?: string
        icon?: string | string[]
        iconRight?: string | string[]
        action?: string
        label?: string
        full?: boolean
        capitalize?: boolean
        tooltip?: string
        dataToSubmit?:object
    }>(),
    {
        style: "primary",
        size: "m",
        capitalize: true,
        dataToSubmit: {}
    }
)

const loadingState = ref(false)
const emits = defineEmits()
const handleClick = (action) => {
    router[action.method](
        action.route,
        props.dataToSubmit,
        {
            onStart: () => { loadingState.value = true },
            onFinish: () => { loadingState.value = false },
            onSuccess: () => { emits('onSuccess')},
            onError: errors => { 
                emits('onError',errors )
                notify({
                    title: "Failed",
                    text: "Error while fetching data",
                    type: "error"
                });
            },
        })
}
</script>

<template>
    <Button v-bind="props" :loading="loadingState" @click="
        handleClick({
            route: route(data[routeName].name, data[routeName].parameters),
            method: 'patch',
        })
        " />
</template>
