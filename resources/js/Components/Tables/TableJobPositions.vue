<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Jun 2023 15:19:26 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {JobPosition} from "@/types/job-position";

const props = defineProps<{
    data: object,
    tab?:string
}>()


function jobPositionRoute(jobPosition: JobPosition) {
    switch (route().current()) {
        case 'grp.hr.job-positions.index':
            return route(
                'grp.hr.job-positions.show',
                [jobPosition.slug]);

    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5"   >
        <template #cell(slug)="{ item: jobPosition }">
            <Link :href="jobPositionRoute(jobPosition)">
                {{ jobPosition['slug'] }}
            </Link>
        </template>



    </Table>
</template>
