<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import Button from "@/Components/Elements/Buttons/Button.vue";
import { Action } from "@/types/Action";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faPencil, faTrashAlt } from "@fal";
import { ref } from "vue";

library.add(
    faPencil,
    faTrashAlt
);


const props = defineProps<{
    action: Action
    dataToSubmit?: any
}>();

const loading = ref(false)

const handleClick = (action) => {
    const href = action.route?.name ? route(action.route?.name, action.route?.parameters) : action.href?.name ? route(action.href?.name, action.href?.parameters) : '#'
    const method = action.route?.method ?? 'get'
    const data = action.route?.method !== 'get' ? props.dataToSubmit : null
    router[method](
        href,
        data,
        {
            onBefore: (visit) => { loading.value = true },
            onerror: ()=>{loading.value = false}
        /*     onFinish: (visit) => { loading.value = false }, */
        })
};


</script>

<template>
    <!-- Button Group -->
    <div v-if="action.type === 'buttonGroup' && action.buttonGroup?.length"
        class="first:rounded-l last:rounded-r overflow-hidden ring-1 ring-gray-300 flex">
        <slot v-for="(button, index) in action.buttonGroup" :name="'button' + index">
            <Link
                :href="button.route?.name ? route(button.route?.name, button.route?.parameters) : action.href?.name ? route(action.href?.name, action.href?.parameters) : '#'"
                class="" :method="button.route?.method ?? 'get'">
            <Button :style="button.style" :label="button.label" :icon="button.icon" :iconRight="button.iconRight"
                :key="`ActionButton${button.label}${button.style}`" :tooltip="button.tooltip"
                class="capitalize inline-flex items-center h-full rounded-none text-sm border-none font-medium shadow-sm focus:ring-transparent focus:ring-offset-transparent focus:ring-0">
            </Button>
            </Link>
        </slot>
    </div>

    <!-- Button -->
    <!--  <Link 
          :href="action.route?.name ? route(action.route?.name, action.route?.parameters) : action.href?.name ? route(action.href?.name, action.href?.parameters) : '#'"
          :method="action.route?.method ?? 'get'"
          :as="action.route?.method ? 'button' : undefined"
          :data="action.route?.method !== 'get' ? dataToSubmit : null"
    > -->
    <Button v-else-if="action.route" @click="handleClick(action)" :style="action.style" :label="action.label"
        :icon="action.icon" :iconRight="action.iconRight" :key="`ActionButton${action.label}${action.style}`"
        :tooltip="action.tooltip" :loading="loading" />
    <!-- </Link> -->
</template>
