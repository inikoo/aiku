<script setup lang="ts">
import { ref, onBeforeMount, watch, onMounted, onUnmounted} from 'vue';
import { cloneDeep, get as getL } from 'lodash';
import Button from '../Elements/Buttons/Button.vue';
import { faSave, faPlus } from '@far/';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { getDatabase, ref as dbRef, set, onValue, get } from 'firebase/database';
import { initializeApp } from "firebase/app"
import serviceAccount from "@/../../private/firebase/aiku-firebase.json"
import { usePage } from "@inertiajs/vue3";
import ColumnsComponents from './Column.vue';


library.add(faSave, faPlus);

const props = defineProps({
  actionRoute: Object,
  theme: String,
  documentName: String,
  data: {
    type: Object,
  },
});


const columns = [
  {
    id: '*',
    name: '*',
    columnType: '*',
    prop: '*',
    readonly: true,
  },
  ...props.data.columns.filter((item) => getL(item, 'hidden', false) === false),
];

const user = ref(usePage().props.auth.user);
const numberInputed = ref(1);
const firebaseApp = initializeApp(serviceAccount);
const db = getDatabase(firebaseApp);
const setData = ref([]);
const focusedCellIndex = ref({ rowIndex: 0, columnIndex: 0 });
const focusedCellMulti = ref([]);

const setFocus = () => {
  const { rowIndex, columnIndex } = focusedCellIndex.value;
  const cells = document.querySelectorAll('.table tbody tr td');
  const targetCell = cells[rowIndex * columns.length + columnIndex];

  if (targetCell) {
    const inputElement = targetCell.querySelector('input, select');
    if (inputElement) {
      inputElement.focus();
    }
  }
};

const updateData = async () => {
  try {
    await set(dbRef(db, props.documentName), setData.value);
  } catch (error) {
    console.error('Error updating data:', error);
  }
};

// Update `setData` with real-time data from Firebase
onValue(dbRef(db, props.documentName), (snapshot) => {
  const data = snapshot.val();
  setData.value = data ? Object.values(data) : [];
});

onValue(dbRef(db, 'focusedIndex'), (snapshot) => {
  const data = snapshot.val();
  focusedCellMulti.value = data ? Object.values(data) : [];
});

watch(setData, updateData, { deep: true });

watch(focusedCellIndex, setFocus);

const updateDataAndSetFocus = () => {
  updateData();
  setFocus();
};

watch([setData, focusedCellIndex,], updateDataAndSetFocus, { deep: true });

const fetchInitialData = async () => {
  try {
    const focusedCellRef = dbRef(db, 'focusedIndex');

    // Fetch initial data for focusedCellMulti
    await get(focusedCellRef).then((snapshot) => {
      if (snapshot.exists()) {
        const data = snapshot.val();
        focusedCellMulti.value = Object.values(data);
      }
    });

    // Fetch initial data for setData
    const documentSnapshot = await get(dbRef(db, props.documentName));
    if (documentSnapshot.exists()) {
      const data = documentSnapshot.val();
      setData.value = Object.values(data);
    } else {
      addMultipleRows();
    }
  } catch (error) {
    console.error('Error fetching initial data:', error);
  }
};



const addMultipleRows = () => {
  const result = [];
  const arrayData = cloneDeep(columns);
  for (let i = 0; i < 5; i++) {
    const dataRes = {};
    for (const field of arrayData) {
      if (field.columnType == 'string') dataRes[field.prop] = '';
      if (field.columnType == 'select') dataRes[field.prop] = { label: '' };
    }
    result.push(dataRes);
  }
  setData.value = [...setData.value, ...result];
};

const addRows = () => {
  const result = [];
  const arrayData = cloneDeep(columns);
  for (let i = 0; i < numberInputed.value; i++) {
    const data = {};
    for (const field of arrayData) {
      data[field.prop] = '';
    }
    result.push(data);
  }
  setData.value = [...setData.value, ...result];
};


onBeforeMount(fetchInitialData);

const handleBeforeUnload = (event) => {
  leavePage();
};

const leavePage = async () => {
  const data = focusedCellMulti.value;
  const focusedCellRef = dbRef(db, 'focusedIndex');
  const index = data.findIndex((item) => item.username === user.value.username);

  if (index > -1) {
    data.splice(index, 1);
  }

  try {
    await set(focusedCellRef, data);
  } catch (error) {
    console.error('Error updating data:', error);
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeyDown);
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(async () => {
  document.removeEventListener('keydown', handleKeyDown);
  window.removeEventListener('beforeunload', handleBeforeUnload);

  // Call the leavePage function before the component is unmounted
  await leavePage();
});

const handleKeyDown = async (event) => {
  const { rowIndex, columnIndex } = focusedCellIndex.value;
  const maxRowIndex = setData.value.length - 1;
  const maxColumnIndex = columns.length - 1;
  const data = focusedCellMulti.value;
  const focusedCellRef = dbRef(db, 'focusedIndex');
  const index = data.findIndex((item) => item.username === user.value.username);

  switch (event.key) {
    case 'ArrowUp':
      if (rowIndex > 0) {
        focusedCellIndex.value = { rowIndex: rowIndex - 1, columnIndex };
        event.preventDefault();
      }
      break;
    case 'ArrowDown':
      if (rowIndex < maxRowIndex) {
        focusedCellIndex.value = { rowIndex: rowIndex + 1, columnIndex };
        event.preventDefault();
      }
      break;
    case 'ArrowLeft':
      if (columnIndex > 0) {
        focusedCellIndex.value = { rowIndex, columnIndex: columnIndex - 1 };
        event.preventDefault();
      }
      break;
    case 'ArrowRight':
      if (columnIndex < maxColumnIndex) {
        focusedCellIndex.value = { rowIndex, columnIndex: columnIndex + 1 };
        event.preventDefault();
      }
      break;
    case 'Enter':
      if (rowIndex < maxRowIndex) {
        focusedCellIndex.value = { rowIndex: rowIndex + 1, columnIndex };
        event.preventDefault();
      }
      break;
  }

  if (index > -1) {
    data[index] = { focusedCellIndex: focusedCellIndex.value, ...user.value };
  } else {
    data.push({ focusedCellIndex: focusedCellIndex.value, ...user.value });
  }

  try {
    await set(focusedCellRef, data);
  } catch (error) {
    console.error('Error updating data:', error);
  }
};


const setFocusedCell = async (rowIndex, columnIndex) => {
  focusedCellIndex.value = { rowIndex, columnIndex };
  const data = focusedCellMulti.value;
  const index = data.findIndex((item) => item.username === user.value.username);
  if (index > -1) {
    // User already exists in the array, update the item
    data[index] = { focusedCellIndex: focusedCellIndex.value, ...user.value };
  } else {
    // User doesn't exist in the array, add a new item
    data.push({ focusedCellIndex: focusedCellIndex.value, ...user.value });
  }
  const focusedCellRef = dbRef(db, 'focusedIndex');

  try {
    await set(focusedCellRef, data);
  } catch (error) {
    console.error('Error updating data:', error);
  }
};

const selected = (row, column) => {
  const data = focusedCellMulti.value.filter((item) => item.username !== user.value.username);
  for (const item of data) {
    if (item.focusedCellIndex.rowIndex === row && item.focusedCellIndex.columnIndex === column) {
      return true;
    }
  }

  return false;
};

const onCopyAll=(position)=>{
  const selectedCellValue = setData.value[position.rowIndex][columns[position.colIndex].prop];
  for(const c of setData.value){
    c[position.column.prop] = selectedCellValue
  }
}

const onCopyRow = (position) => {
  const selectedCellValue = setData.value[position.column.rowIndex][columns[position.column.colIndex].prop];
  for (let c = position.startColumnIndex; c <= position.endColumnIndex; c++) {
    setData.value[c][position.column.column.prop]  = selectedCellValue
  }
};

const onCopyAllEmpty = (position) => {
  const selectedCellValue = setData.value[position.rowIndex][columns[position.colIndex].prop];
  for(const c of setData.value){
    if( c[position.column.prop] == "") c[position.column.prop] = selectedCellValue
  }
}

</script>

<template>
  <div>
    <table class="table">
      <thead>
        <tr>
          <th v-for="(column, index) in columns" :key="index" class="px-4 py-2">
            {{ column.name }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, rowIndex) in setData" :key="rowIndex">
          <td v-for="(column, colIndex) in columns" :key="colIndex" :class="{
            'px-4': true,
            'py-2': true,
            'selected': selected(rowIndex, colIndex)
          }" :tabindex="(rowIndex === focusedCellIndex.rowIndex && colIndex === focusedCellIndex.columnIndex) ? 0 : -1"
            @focus="setFocusedCell(rowIndex, colIndex)" @click="setFocusedCell(rowIndex, colIndex)">
           <ColumnsComponents
           :onCopyAll = "onCopyAll"
           :onCopyRow ="onCopyRow"
           :rowIndex = "rowIndex"
           :colIndex = "colIndex"
           :column = "column"
           :setData = "setData"
           :row = "row"
           :updateDataAndSetFocus = "updateDataAndSetFocus"
           :onCopyAllEmpty="onCopyAllEmpty"
           :isDisable = "selected(rowIndex,colIndex)"
           />
          </td>
        </tr>
      </tbody>

    </table>
  </div>
  <div class="pt-4 pb-6 pl-1 pr-0 flex">
    <button type="button" @click="addRows"
      class="flex rounded-r-none items-center gap-3 bg-white border border-gray-300 py-2 px-4 rounded">
      <FontAwesomeIcon icon="far fa-plus" />
      <span>Add Row</span>
    </button>
    <input type="number" v-model="numberInputed"
      class="border border-gray-300 px-4 py-2 rounded-l-none rounded rounded-l-none custom-input" />

  </div>
</template>

<style>
.table {
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  border: 1px solid #e2e8f0;
}

.px-4 {
  padding-left: 1rem;
  padding-right: 1rem;
}

.table td {
  border: 1px solid #e2e8f0;
  padding: 4px;
}

.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}


.input {
  border: 0px;
  padding: 0.5rem;
  width: 100%;
}

.custom-input {
  border-left: none;
  width: 120px;
}

.selected {
  background-color: red;
}

:root {
  --popper-theme-background-color: #ffffff;
  --popper-theme-background-color-hover: #ffffff;
  --popper-theme-text-color: #333333;
  --popper-theme-border-width: 1px;
  --popper-theme-border-style: solid;
  --popper-theme-border-color: #eeeeee;
  --popper-theme-border-radius: 6px;
  --popper-theme-padding: 10px;
  --popper-theme-box-shadow: 0 6px 30px -6px rgba(0, 0, 0, 0.25);
  }
</style>

