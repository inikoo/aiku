<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 08 Feb 2024 12:36:21 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import {
    faExternalLink
} from '@fal'
import {library} from "@fortawesome/fontawesome-svg-core";
library.add(
    faExternalLink
)

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

const props = defineProps<{
    data: {
        slug: string
        url: string
        domain: string
        state: string
        status: string
        created_at: string
        updated_at: string
    }
}>()


</script>

<template>
    <div class="px-6 py-24 sm:py-20 lg:px-8">
        {{data.url}}
        <a :href="data.url" target="_blank">
        <FontAwesomeIcon class="ml-3" icon="fal fa-external-link" aria-hidden="true" />
        </a>
    </div>
</template>
