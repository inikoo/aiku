<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import { faCube, faStar } from "@fas"
  import { library } from "@fortawesome/fontawesome-svg-core"
  import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
  
  
  library.add(faCube, faStar)
  
  const props = defineProps<{
      modelValue: any
  }>()
  
  
  </script>
  
  <template>
      <div class="aspect-[1902/683] mb-6 overflow-hidden flex items-center">
            <img src="https://tailwindui.com/img/ecommerce-images/home-page-03-category-02.jpg" class="w-full"
                alt="Product Image">
        </div>
  </template>