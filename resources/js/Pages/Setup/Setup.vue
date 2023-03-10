<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Fri, 12 Aug 2022 15:41:03 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Inikoo
  -  Version 4.0
  -->

<script setup>

import {Head} from '@inertiajs/vue3';
import SetupUsername from '@/Components/Setup/SetupUsername.vue';
import SetupAccessCode from '@/Components/Setup/SetupAccessCode.vue';

</script>

<template layout="App">
    <Head title="Setup"/>
    <SetupAccessCode  v-if="typeof $page.props.auth.user.username=== 'string'  && $page.props.auth.user.username!='' " />
    <SetupUsername v-else/>

</template>

