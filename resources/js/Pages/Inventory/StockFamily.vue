<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 22 Oct 2022 18:57:31 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->



<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {useLocaleStore} from '@/Stores/locale.js';

defineProps(["title","pageHead","stockFamily"])

import {library} from '@fortawesome/fontawesome-svg-core';
import {faInventory,faBox} from '@/../private/pro-light-svg-icons';
library.add(faInventory,faBox);

const locale = useLocaleStore();


</script>


<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>




</template>
