<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 19 Feb 2024 21:18:50 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Stats from "@/Components/DataDisplay/Stats.vue"
import { capitalize } from "@/Composables/capitalize"
import { trans } from "laravel-vue-i18n"
import { useFormatTime } from "@/Composables/useFormatTime"
import { PageHeading as PageHeadingTypes } from "@/types/PageHeading"
import { PalletCustomer, FulfilmentCustomerStats } from '@/types/Pallet'


const props = defineProps<{
    title: string
    pageHead : PageHeadingTypes
    customer: PalletCustomer
	stats : {}
}>()


</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead"></PageHeading>
	<stats class="ml-4 pb-2" :stats="stats" />
		<div class="grid ml-4 grid-cols-1 gap-5 sm:grid-cols-3">
			<div
				class="h-fit bg-slate-50 border border-slate-200 text-retina-600 p-6 flex flex-col justify-between rounded-lg shadow overflow-hidden">
				<div class="w-full">
					<h2 v-if="customer?.name" class="text-3xl font-bold">{{ customer?.name }}</h2>
					<h2 v-else class="text-3xl font-light italic brightness-75">
						{{ trans("No name") }}
					</h2>
					<div class="text-lg">
						{{ customer?.shop }}
					</div>
				</div>

				<div class="mt-4 space-y-3 text-sm text-slate-500">
					<div class="border-l-2 border-slate-500 pl-4">
						<h3 class="font-light">Member since</h3>
						<address class="text-base font-medium not-italic text-gray-600">
							<p>{{ useFormatTime(customer?.created_at) || "-" }}</p>
						</address>
					</div>

					<div class="border-l-2 border-slate-500 pl-4">
						<h3 class="font-light">{{ trans("Billing Cycle") }}</h3>
						<address class="text-base font-medium not-italic text-gray-600 capitalize">
							<p>{{ rental_agreement?.billing_cycle }}</p>
						</address>
					</div>

					<div class="border-l-2 border-slate-500 pl-4">
						<h3 class="font-light">{{ trans("Pallet Limit") }}</h3>
						<address class="text-base font-medium not-italic text-gray-600">
							<p>{{ rental_agreement?.pallets_limit || `(${trans("No limit")})` }}</p>
						</address>
					</div>
				</div>
			</div>
		</div>

</template>
