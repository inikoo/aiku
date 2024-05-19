<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 10 Apr 2024 16:08:46 Central Indonesia Time, Sanur , Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"

import { capitalize } from "@/Composables/capitalize"
import TableSelectPaymentServiceProviders from "@/Components/Tables/Grp/Org/Accounting/TableSelectPaymentServiceProviders.vue"

const props = defineProps(["data", "title", "pageHead", 'paymentAccountTypes', 'organisation_id'])
</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <TableSelectPaymentServiceProviders :data="data" :paymentAccountTypes="paymentAccountTypes"
        :organisation_id="props.organisation_id" />
</template>
