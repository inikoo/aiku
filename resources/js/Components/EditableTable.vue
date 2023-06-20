<script setup lang="ts">
import {  ref, computed, onMounted } from 'vue';
import VGrid from '@revolist/vue3-datagrid';
import { cloneDeep } from 'lodash';
import Button from './Elements/Buttons/Button.vue';

const props = defineProps({
    data: {
        type: Object,
        required: true,
        default: () => ({
            value: [
                {
                    name: '',
                    details: '',
                },
                {
                    name: '',
                    details: '',
                },
            ],
            columns: [
                {
                    prop: 'name',
                    name: 'First',
                },
                {
                    prop: 'details',
                    name: 'Second',
                },
            ],
            theme: 'compact',
        }),
    },
});

const addMultipleRows = () => {
  const result = [];
  const arrayData = cloneDeep(props.data.columns);
  for (let i = 0; i < 20; i++) {
    const data = {};
    for (const field of arrayData) {
      data[field.prop] = '';
    }
    result.push(data);
  }
  return result;
};


let vgrid = ref()
  let gRowIndex = ref(0)
  let gColName = ref('')

  const onBeforeEditStart = (e: CustomEvent<{rowIndex: number, prop: string}>) => {
    gRowIndex.value = e.detail.rowIndex
    gColName.value = e.detail.prop
  }

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const onFocusOut = async (e: any) => { // I don't know about this event, no docs
    // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
    let viewData = await vgrid.value.$el.getVisibleSource()
    viewData[gRowIndex.value][gColName.value] = e.target.value
    const set = vgrid.value.source;
  }

  const handleSave = () => {
  const editableRows = [];
  for (const row of setData.value) {
    let isRowEditable = false;
    for (const column of props.data.columns) {
      if (row[column.prop] !== '') {
        isRowEditable = true;
        break;
      }
    }
    if (isRowEditable) {
      editableRows.push(row);
    }
  }
  console.log(editableRows)
};;

const setData = ref(addMultipleRows());

</script>

<template>
    <div class="flex justify-end">
    <Button  @click="handleSave" >Save</Button>
    </div>
    <div>
        <v-grid ref="vgrid" @beforeeditstart="onBeforeEditStart" @focusout="onFocusOut" :theme="props.data.theme"
            :source="setData" :columns="props.data.columns"></v-grid>
    </div>
</template>

<style>
revo-grid {
    height: 800px;
}
</style>
