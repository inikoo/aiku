<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faExclamationCircle } from "@fortawesome/free-solid-svg-icons"
import { capitalize } from "@/Composables/capitalize"
import { inject, ref, computed, onMounted, onUnmounted } from "vue"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import {
	faBoothCurtain,
	faBoxes,
	faBoxOpen,
	faBrowser,
	faCoin,
	faDolly,
	faEnvelope,
	faEnvelopeOpenText,
	faExchangeAlt,
	faFilter,
	faForklift,
	faInboxOut,
	faIndustryAlt,
	faInventory,
	faLocationArrow,
	faMailBulk,
	faRoad,
	faTrashAlt,
	faTruckLoading,
	faUser,
	faUserAlien,
	faUserCircle,
	faUserHeadset,
	faUsers,
	faWarehouseAlt,
	faPaperPlane,
	faScrollOld,
	faPhoneVolume,
	faRaygun,
} from "@fal"
import SectionTable from "@/Components/Table/SectionTable.vue"
import { faAngleDown, faAngleUp } from "@far"
import DashboardCard from "@/Components/DataDisplay/DashboardCard.vue"

const props = defineProps<{
	title: string
	pageHead: any
	dashboard: {
		columns: Array<{
			widgets: Array<{
				type: string
				data: {
					data: Array<{
						section: string
						data: Array<{
							name: string
							icon: string
							route: string
							count: number
						}>
					}>
				}
			}>
		}>
	}
}>()

const locale = inject("locale", aikuLocaleStructure)

library.add(
	faExclamationCircle,
	faInboxOut,
	faUser,
	faFilter,
	faBoxes,
	faAngleDown,
	faAngleUp,
	faBrowser,
	faTrashAlt,
	faCoin,
	faBoothCurtain,
	faRoad,
	faUserAlien,
	faEnvelopeOpenText,
	faEnvelope,
	faUserCircle,
	faExchangeAlt,
	faDolly,
	faBoxOpen,
	faTruckLoading,
	faForklift,
	faUsers,
	faUserHeadset,
	faWarehouseAlt,
	faInventory,
	faLocationArrow,
	faIndustryAlt,
	faMailBulk,
	faPaperPlane,
	faPhoneVolume,
	faRaygun,
	faScrollOld
)

// Search functionality
const searchQuery = ref("")
const isFilterVisible = ref(false)

const handleClickOutside = (event: Event) => {
	const target = event.target as HTMLElement
	if (!target.closest(".search-container")) {
		isFilterVisible.value = false
	}
}

onMounted(() => {
	document.addEventListener("click", handleClickOutside)
})

onUnmounted(() => {
	document.removeEventListener("click", handleClickOutside)
})

const columnClasses = computed(() => {
  const totalColumns = props.dashboard?.columns?.length || 1;
  const columnSpan = Math.floor(12 / totalColumns);
  return `col-span-${columnSpan}`;
});
</script>

<template>
	<!-- Page Header -->
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead" />

	<!-- Dashboard Grid -->
	<div class="grid grid-cols-12 m-3 gap-4">
		<template v-for="(column, colIndex) in props.dashboard.columns" :key="colIndex">
      <div :class="columnClasses" class="space-y-4">
        <!-- Loop through widgets in each column -->
        <template v-for="(widget, widgetIndex) in column.widgets" :key="widgetIndex">
          <!-- Render Overview Table -->
          <div v-if="widget.type === 'overview_table'">
            <SectionTable :data="widget.data.data" />
          </div>

		  <DashboardCard
            v-else-if="widget.type === 'card_currency' || widget.type === 'card_percentage'"
            :value="widget.value"
            :description="widget.label"
            :showRedBorder="widget.type === 'card_currency'"
            :showIcon="widget.type === 'card_currency'"
          />

          <!-- Fallback for unknown widget types -->
         
        </template>
      </div>
    </template>
  </div>
</template>

<style scoped>
.search-container {
	transition: width 0.3s ease;
}
</style>
