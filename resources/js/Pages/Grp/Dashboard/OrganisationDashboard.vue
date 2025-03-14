<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 18 Jan 2024 15:36:09 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref } from "vue"
import { faChevronDown } from "@far"
import { faPlay, faTriangle } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { Head } from "@inertiajs/vue3"
import { trans } from "laravel-vue-i18n"
import { faSortDown, faSortUp } from "@fas"
import Dashboard from "@/Components/DataDisplay/Dashboard/Dashboard.vue"
import { faSitemap } from "@fal"

library.add(faTriangle, faChevronDown, faSortDown, faSortUp, faPlay, faSitemap)

defineProps<{
	dashboard: any
	interval_options: {
		label: string
		labelShort: string
		value: string
	}[]
	dashboard_stats:{}
}>()



const checked = ref(true)



</script>

<template>
	<Head :title="trans('Dashboard')" />
	<div class="grid grid-cols-12 m-3 gap-4">

     <pre>{{ dashboard }}</pre>

				<div class="col-span-12">
					<Dashboard 
						:dashboard="dashboard_stats"
						:checked="checked"
						tableType="org"/>	
				</div>
		

	</div>
</template>

