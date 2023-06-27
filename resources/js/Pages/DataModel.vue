<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 03:19:52 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import { ref } from 'vue';

  const props = defineProps<{
      data:object
  }>()
  console.log(props)

  const current = ref(0)
  </script>


  <template layout="App">
      <div>{{data}}</div>
  </template>



