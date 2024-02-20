<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Jun 2023 11:37:50 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {useLocaleStore} from '@/Stores/locale';
import { capitalize } from "@/Composables/capitalize"


const locale = useLocaleStore();


const props = defineProps<{
    title: string,
    pageHead: object,
}>()


</script>


<template layout="Grp">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>

</template>
