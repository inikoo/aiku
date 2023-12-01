<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 13 Jan 2022 06:35:14 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Inikoo
  -  Version 4.0
  -->

<script setup>

import Error from '@/Pages/Utils/Error.vue';
import { capitalize } from "@/Composables/capitalize"

const props = defineProps({
                              status     : Number,
                              title      : String,
                              description: String,
                          });

</script>

<template layout="App">
   <Error :status="status"  :title="capitalize(title)"  :description="description" ></Error>
</template>




