<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 14 Sept 2022 01:56:59 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>




</template>

<script setup>
import { Head,Link } from '@inertiajs/inertia-vue3';

import PageHeading from '@/Components/Headings/PageHeading.vue';


defineProps(["title","pageHead","guest"])

</script>
