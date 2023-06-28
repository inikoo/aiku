<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 03:19:52 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
-->

<script setup lang="ts">
import { ref, onBeforeMount } from 'vue';
import moment from 'moment';

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
        job_position_scopes: string[];
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
  if (key.endsWith('_at')) {
    const date = moment(props.data[key]).format('MMMM Do YYYY, h:mm')
    return date == 'Invalid date' ? '-' : date
  }
  return props.data[key];
};


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
    
    <div class="flex flex-col md:flex-row p-4">
      <div class="w-full">
        <table class="table-auto w-full table">
          <tbody>
            <slot>
              <tr
                v-for="(row, rowIndex) in newData.slice(0, Math.ceil(newData.length / 2))"
                :key="rowIndex"
                class="bg-white divide-gray-200 border-r-2  md:border-r-0"
              >
                <td class="px-4 py-2 border border-gray-200 p-6 bg-gray-50 title">{{ row.label }}</td>
                <td class="px-4 py-2 border border-gray-200 border-r-2 md:border-r-0 ">{{ row.value == null ? '-' : row.value }}</td>
              </tr>
            </slot>
          </tbody>
        </table>
      </div>
      <div class="w-full">
        <table class="table-auto w-full table">
          <tbody>
            <slot>
              <tr
                v-for="(row, rowIndex) in newData.slice(Math.ceil(newData.length / 2))"
                :key="rowIndex"
                class="bg-white divide-gray-200"
              >
                <td class="px-4 py-2 border border-gray-200 p-6 bg-gray-50 title" >{{ row.label }}</td>
                <td class="px-4 py-2 border border-gray-200">{{ row.value == null ? '-' : row.value }}</td>
              </tr>
            </slot>
          </tbody>
        </table>
      </div>
    </div>
  </template>
  
  

<style>
.table td {
    font-size: 14px;
}

.title {
    font-weight: 500;
    width: 400px;
}
</style>
