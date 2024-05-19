<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 20 Sep 2023 13:50:48 Malaysia Time, Pantai Lembeng, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->
<script setup lang="ts">

import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {useLocaleStore} from "@/Stores/locale";


const props = defineProps<{
    item: {
        label?: string
        number?: string
    }
}>()

const locale = useLocaleStore()

</script>

<template>
    <span class="leading-none">{{ item.label  }}</span>
    <template v-if="typeof item.number == 'number'">
        (<span v-if="item.number">{{ locale.number(item.number) }}</span>
        <FontAwesomeIcon v-else icon="fal fa-empty-set"/>)
    </template>
</template>
