<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 03:19:52 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
-->

<script setup lang="ts">
import { useFormatTime } from '@/Composables/useFormatTime'
import { ref, onBeforeMount } from 'vue'

const props = defineProps<{
    data: {
        contact_name: string;
        created_at: string;
        data: string[];
        date_of_birth: string;
        deleted_at: string;
        email: string;
        emergency_contact: string;
        employment_end_at: string;
        employment_start_at: string;
        errors: string[];
        gender: string;
        identity_document_number: string;
        identity_document_type: string;
        job_title: string;
        phone: string;
        salary: string;
        slug: string;
        state: string;
        type: string;
        updated_at: string;
        user: string;
        week_working_hours: string;
        worker_number: string;
        working_hours: string[];
    };
}>()
let newData = ref([])

const setValue = (key) => {
    if (key.endsWith('_at')) { // created_at, updated_at, deleted_at
        useFormatTime(props.data[key])
        return useFormatTime(props.data[key])
    }
    return props.data[key]
}


const setDataToObject = () => {
    let setData = [];
    for (const key in props.data) {
        const object = {
            label: key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()),
            value: setValue(key),
            key: key
        };
        setData.push(object)
    }
    newData = setData
}



onBeforeMount(setDataToObject);

const current = ref(0);
</script>

<template>
   <div class="px-7">
    <div class="border-t-0 border-gray-100 w-full  md:w-2/5">
      <dl class="divide-y divide-gray-100">
        <div v-for="(item, key) in newData" :key="item.label" class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
          <dt class="text-sm font-medium leading-6 text-gray-900">{{ item.label }}</dt>
          <dd class="mt-1 text-sm leading-6 text-gray-500 sm:col-span-2 sm:mt-0">{{ item.value == null ? '-' : item.value }}</dd>
        </div>
      </dl>
    </div>
  </div>

</template>
  
  


