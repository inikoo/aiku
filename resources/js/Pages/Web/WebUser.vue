<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 24 Nov 2022 10:39:51 Central Indonesia Time, Ubud, Bali, Indonesia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/inertia-vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {library} from '@fortawesome/fontawesome-svg-core';
import {faGlobe} from '@/../private/pro-light-svg-icons';

library.add(faGlobe);

const props = defineProps(['title', 'pageHead', 'customer']);



</script>


<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>

</template>

