<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 14:00:48 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import QrcodeVue from 'qrcode.vue'

const props = defineProps<{
    data: {
        slug: String,
        qr_code: String,
        status: String,
        name: String,
        type: String,
        device_name: String
        device_uuid: String
    },
}>()

console.log(props.data)
</script>


<template>
    <div v-if="data.status === 'disconnected'" class="relative w-fit p-4 bg-white shadow rounded-lg flex flex-col items-center gap-y-2">
        <QrcodeVue :value="data.qr_code" :size="200" level="L" render-as="svg" foreground="#334155"
            class="p-2 bg-gray-100 rounded-md" />
        <div class="text-gray-800 font-medium">
            <p>{{ data.qr_code }}</p>
        </div>
    </div>
    <div class="m-4" v-else>
        You connected using device: {{ data.device_name }}
    </div>
</template>