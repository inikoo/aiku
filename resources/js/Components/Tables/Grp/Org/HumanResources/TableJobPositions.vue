<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 12 May 2024 15:26:39 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
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
        case 'grp.org.hr.job-positions.index':
            return route(
                'grp.org.hr.job-positions.show',
                [
                    route().params['organisation'],
                    jobPosition.slug
                ]);

    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5"   >
        <template #cell(code)="{ item: jobPosition }">
            <Link :href="jobPositionRoute(jobPosition)" class="specialUnderline">
                {{ jobPosition['code'] }}
            </Link>
        </template>



    </Table>
</template>
