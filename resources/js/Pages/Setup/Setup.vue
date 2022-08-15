<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Fri, 12 Aug 2022 15:41:03 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Inikoo
  -  Version 4.0
  -->

<template layout="App">
    <Head title="Setup"/>

    <SetupUsername v-if="$page.props.auth.user.username===''"/>
    <SetupErpGateway v-else />

</template>

<script setup>

import {Head} from '@inertiajs/inertia-vue3';
import SetupUsername from '@/Components/Setup/SetupUsername.vue';
import SetupErpGateway from '@/Components/Setup/SetupErpGateway.vue';




</script>
