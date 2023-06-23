<script setup lang="ts">
import { ref, onBeforeMount, watch, onMounted, onUnmounted, defineProps } from 'vue';
import { cloneDeep, get as getL } from 'lodash';
import Button from '../Elements/Buttons/Button.vue';
import { faSave, faPlus } from '@/../private/pro-regular-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { getDatabase, ref as dbRef, set, onValue, get } from 'firebase/database';
import { initializeApp } from "firebase/app"
import serviceAccount from "@/../private/firebase/aiku-firebase.json"
import Multiselect from "@vueform/multiselect"

library.add(faSave, faPlus);

const props = defineProps({
  actionRoute: Object,
  theme: String,
  documentName: String,
  data: {
    type: Object,
  },
});

console.log(props)

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

const numberInputed = ref(1);
const firebaseApp = initializeApp(serviceAccount);
const db = getDatabase(firebaseApp);
const setData = ref([]);
const focusedCellIndex = ref({ rowIndex: 0, columnIndex: 0 });

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
    console.log('Data successfully updated!', setData.value);
  } catch (error) {
    console.error('Error updating data:', error);
  }
};

// Update `setData` with real-time data from Firebase
onValue(dbRef(db, props.documentName), (snapshot) => {
  const data = snapshot.val();
  setData.value = data ? Object.values(data) : [];
});

watch(setData, updateData, { deep: true });

watch(focusedCellIndex, setFocus);

const updateDataAndSetFocus = () => {
  updateData();
  setFocus();
};

watch([setData, focusedCellIndex], updateDataAndSetFocus, { deep: true });

const fetchInitialData = async () => {
  try {
    const snapshot = await get(dbRef(db, props.documentName));
    if (snapshot.exists()) {
      const data = snapshot.val();
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
    console.log(dataRes)
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

const tableRef = ref(null);

onBeforeMount(fetchInitialData);

onMounted(() => {
  document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyDown);
});

const handleKeyDown = (event) => {
  const { rowIndex, columnIndex } = focusedCellIndex.value;
  const maxRowIndex = setData.value.length - 1;
  const maxColumnIndex = columns.length - 1;

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
  }
};
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
          <td
            v-for="(column, colIndex) in columns"
            :key="colIndex"
            class="px-4 py-2"
            :tabindex="(rowIndex === focusedCellIndex.rowIndex && colIndex === focusedCellIndex.columnIndex) ? 0 : -1"
            @focus="focusedCellIndex = { rowIndex, columnIndex: colIndex }"
          >
            <div v-if="column.readonly">{{ row[column.prop] }}</div>
            <div v-if="column.columnType === 'string'">
              <input type="text" v-model="row[column.prop]" class="input" />
            </div>
            <div v-if="column.columnType === 'select'">
              <select v-model="row[column.prop]" class="input">
                <option
                  v-for="option in column.options"
                  :value="option.label"
                  :key="option.label"
                >
                  {{ option.label }}
                </option>
              </select>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="pt-4 pb-6 pl-1 pr-0 flex">
    <button
      type="button"
      @click="addRows"
      class="flex rounded-r-none items-center gap-3 bg-white border border-gray-300 py-2 px-4 rounded"
    >
      <FontAwesomeIcon icon="far fa-plus" />
      <span>Add Row</span>
    </button>
    <input
      type="number"
      v-model="numberInputed"
      class="border border-gray-300 px-4 py-2 rounded-l-none rounded rounded-l-none custom-input"
    />
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
  padding: 0;
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
</style>
