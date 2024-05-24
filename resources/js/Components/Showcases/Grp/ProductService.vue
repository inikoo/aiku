<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
  import { faCircle } from '@fas'
  import { library } from '@fortawesome/fontawesome-svg-core'
  library.add(faCircle)
  
  const props = defineProps<{
      data: {}
  }>()
  
  
  </script>
  
  
  <template>
      <div>
        Product Service
        <br>
        <pre>{{ data }}</pre>
      </div>
  
  </template>
  