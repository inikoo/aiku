<template>
	<div class="col-span-12">
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
			<!-- Responsive grid layout -->
			<DashboardCard
				v-for="(org, index) in props.groupStats.organisations.filter(
					(org) => org.type !== 'agent'
				)"
				:key="index"
				:value="
					useLocaleStore().currencyFormat(
						groupStats.currency.code,
						org.interval_percentages?.sales?.[selectedDateOption]?.amount || 0
					)
				"
				:description="`Sales for ${org.name}`"
				:showRedBorder="
					isNegative(org.interval_percentages?.sales?.[selectedDateOption]?.amount || 0)
				"
				:showIcon="
					isNegative(org.interval_percentages?.sales?.[selectedDateOption]?.amount || 0)
				" />
		</div>
	</div>
</template>

<script setup lang="ts"></script>

<style scoped></style>
