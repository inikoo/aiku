<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 17 May 2024 13:09:02 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import Icon from "@/Components/Icon.vue"
import StoredItemProperty from '@/Components/StoredItemsProperty.vue'
import type { Meta, Links } from "@/types/Table"
import { Pallet } from "@/types/Pallet"
import { useFormatTime } from '@/Composables/useFormatTime'
import Button from "@/Components/Elements/Buttons/Button.vue"
import Popover from '@/Components/Popover.vue'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faTrashAlt } from "@far"
import { faCheckCircle } from "@fas"
import { faSignOutAlt, faSpellCheck, faCheck, faTimes, faCheckDouble, faCross, faFragile, faGhost, faBoxUp, faStickyNote, } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

import { routeType } from "@/types/route"

library.add(faTrashAlt, faSignOutAlt, faSpellCheck, faCheck, faTimes, faCheckDouble, faCross, faFragile, faGhost, faBoxUp, faStickyNote, faCheckCircle)


const props = defineProps<{
    data: {},
    tab?: string
}>()

</script>

<template>
    <!-- <pre>{{ props.data.data[0] }}</pre> -->
    <Table :resource="data" :name="tab" class="mt-5">
    </Table>
</template>