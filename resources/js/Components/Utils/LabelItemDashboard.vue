<script setup lang="ts">
import { useLocaleStore } from "@/Stores/locale";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { Link } from "@inertiajs/vue3";

const props = defineProps<{
	dataTable?: any
    settings?: any
	type?: string
    locale?: any
}>()

function RouteDashboardTable(shop: any, type: string) {	
    if (type === 'invoices') {
        return route(shop?.route_invoice?.name, shop?.route_invoice?.parameters)
    } else if (type === 'refunds') {
        return route(shop?.route_refund?.name, shop?.route_invoice?.parameters)
        
    }
}

</script>

<template>
	<Transition name="spin-to-down" mode="out-in">
		<div
            v-if="type === 'sales'"
			v-tooltip="
				useLocaleStore().currencyFormat(
					dataTable.currency_code,
					dataTable.interval_percentages?.[type]?.amount || 0
				)
			"
			:key="dataTable.interval_percentages?.[type]?.amount">
			<p>
				{{
					useLocaleStore().CurrencyShort(
						dataTable.currency_code,
						dataTable.interval_percentages?.[type]?.amount || 0,
						settings.selected_amount
					)
				}}
			</p>
		</div>
        <div v-else-if="type === 'refunds' || type ==='invoices'" :key="dataTable.interval_percentages?.[type]?.amount || 0">
            <Link v-if="dataTable.interval_percentages?.[type]?.amount" :href="RouteDashboardTable(dataTable, type)" class="hover-underline text-[16px] md:text-[18px]" >
                {{
                    locale.number(
                        dataTable?.interval_percentages?.[type]?.amount || 0
                    )
                }}
            </Link>
            <span v-else class="text-[16px] md:text-[18px]">
                {{
                    locale.number(
                        dataTable?.interval_percentages?.[type]?.amount || 0
                    )
                }}
            </span>
        </div>
       
	</Transition>
</template>

<style scoped>
.hover-underline:hover {
  text-decoration: underline;
}

</style>