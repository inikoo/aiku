<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 24 Jun 2023 11:02:25 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import { capitalize } from "@/Composables/capitalize";
import RemoveModelAction from "@/Components/Actions/RemoveModelAction.vue";

const props = defineProps<{
    title: string,
    pageHead: {
        title: string
        exitEdit: {
            route: {
                name: string
                parameters: string[]
            }
        }
    },
    "actions": Array<{
        type: string,
        route: {
            name: string
            parameters: string[]
        }

    }>


}>();

</script>

<template layout="App">

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <RemoveModelAction class="ml-4 mb-6" v-for="action in actions" :action="action"></RemoveModelAction>

</template>
