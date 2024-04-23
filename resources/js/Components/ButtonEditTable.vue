<!--
  Author: Raul Perusquia <raul@inikoo.com>
  Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  Copyright (c) 2023, Raul A Perusquia Flores
-->

<script setup lang="ts">
import { withDefaults, ref } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"

const props = withDefaults(defineProps<{
    data: {}
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
    dataToSubmit?: {}
}>(), {
    style: "primary",
    size: "m",
    capitalize: true,
    dataToSubmit: {}
})

const emits = defineEmits<{
    (e: 'onSuccess'): void
    (e: 'onError', errors: any): void
}>()

const loadingState = ref(false)
const handleClick = () => {
    router.patch(
        route(props.data[props.routeName].name, props.data[props.routeName].parameters),
        props.dataToSubmit,
        {
            preserveScroll: true,
            onStart: () => { loadingState.value = true },
            onSuccess: () => { emits('onSuccess') },
            onError: errors => {
                emits('onError', errors)
                notify({
                    title: "Failed",
                    text: "Error while fetching data",
                    type: "error"
                })
            },
            onFinish: () => { loadingState.value = false },
        })
}

</script>

<template>
    <Button v-bind="props" :loading="loadingState" @click="() => handleClick()" />
</template>
