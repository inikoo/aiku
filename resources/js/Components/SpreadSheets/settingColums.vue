<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Jun 2023 10:16:56 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import { ref } from 'vue';
  import { cloneDeep } from 'lodash';
  
  const props = defineProps({
    onCopyAll: {
      type: String,
      required: true
    },
    onCopyRow: {
      type: String,
      required: true
    },
    onCopyAllEmpty: {
      type: String,
      required: true
    },
    column: Object,
    lengthData: Number,
  });

  const emits = defineEmits(['copyAll', 'copyRow', 'onCopyAllEmpty']);

//   const startColumnIndex = ref(cloneDeep(props.column).rowIndex + 1);
//   const endColumnIndex = ref(cloneDeep(props.lengthData));

  
  const onCopyAll = (column) => {
    emits('copyAll', column);
};

const onCopyAllEmpty = (column) => {
  emits('onCopyAllEmpty', column);
};

const onCopyRow = (position) => {
    emits('copyRow', position);
};
  
  </script>
  
  <template>
    <div>
      <ul class="list-none">
        <li @click="() =>onCopyAll(column)">Replace to all</li>
        <li @click="props.onCopyAllEmpty(column)">Replace to the empty Fields</li>
        <!-- <li @click="onCopyRow({endColumnIndex : endColumnIndex , startColumnIndex : startColumnIndex, column})">
          Copy to column 
          <input type="number" class="input-setting" v-model="startColumnIndex" @click.stop  :min="1" />
          until 
          <input type="number" class="input-setting" v-model="endColumnIndex" :min="props.column.rowIndex + 1"  :max="props.lengthData" @click.stop/>
        </li> -->
      </ul>
    </div>
  </template>
  
  <style>
  .input-setting {
    height: 24px;
    width: 100px;
  }
  
  .popper[data-v-5784ed69] {
    font-size: 15px;
  }
  </style>
  
  