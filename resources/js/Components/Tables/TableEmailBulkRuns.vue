<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Mailshot} from "@/types/mailshot";
import icon from '@/Components/Icon.vue'
import { faSpellCheck, faSeedling, faPaperPlane, faStop } from '@fal'
import { library } from "@fortawesome/fontawesome-svg-core";
library.add(faSpellCheck, faSeedling, faPaperPlane, faStop )

const props = defineProps<{
    data: object,
    tab?: string
}>()

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
    </Table>
</template>


