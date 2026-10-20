<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 12:20:38 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import { computed, ref } from "vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import TableOrgSuppliers from "@/Components/Tables/Grp/Org/Procurement/TableOrgSuppliers.vue"
import AgentSuppliersDashboard from "@/Components/Procurement/AgentSuppliersDashboard.vue"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { useTabChange } from "@/Composables/tab-change"
import { capitalize } from "@/Composables/capitalize"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPersonDolly, faTachometerAlt, faBars } from "@fal"
library.add(faPersonDolly, faTachometerAlt, faBars)

const props = defineProps<{
	pageHead: object
	title: string
	data?: object
	tabs?: {
		current: string
		navigation: object
	}
	dashboard?: object
	index?: object
}>()

const currentTab = ref(props.tabs?.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const component = computed(() => ({
	dashboard: AgentSuppliersDashboard,
	index: TableOrgSuppliers,
})[currentTab.value as string])
</script>

<template>
	<div>
		<Head :title="capitalize(title)" />
		<PageHeading :data="pageHead" />
		<template v-if="tabs">
			<Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />
			<component :is="component" v-if="props[currentTab]" :data="props[currentTab]" :tab="currentTab" />
		</template>
		<TableOrgSuppliers v-else :data="data" />
	</div>
</template>
