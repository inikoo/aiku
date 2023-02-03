<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 10 Sept 2022 13:07:27 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<template>


    <div>
        <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div v-for="item in stats" :key="item.name" class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500 capitalize">{{ item.name }}</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                    <Link class="special-underline" v-if="item.href" :href="route(item.href[0])">{{ item.stat }}</Link>
                    <span v-else>{{ item.stat }}</span>
                </dd>
            </div>
        </dl>
    </div>


</template>

<script setup>
import {Link} from '@inertiajs/vue3';

defineProps(['stats']);

</script>
