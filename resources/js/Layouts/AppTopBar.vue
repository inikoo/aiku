<template>
	<div class="flex justify-between items-center">
		<div class="block pb-3 pl-3 xl:w-56">
			<img class="h-4 mt-4 xl:h-6" src="/art/logo-color-trimmed.png" alt="Aiku" />
			<span class="font-logo mb-1 mr-2 xl:hidden whitespace-nowrap text-sm">
				{{ props.tenantName }}
			</span>
		</div>
		<component :is="component[currentUrl]" />
	</div>
</template>

<script setup lang='ts'>
import { ref } from 'vue'
import { router } from "@inertiajs/vue3";
import Shops from '@/Components/Navigation/TopBarMenu/TopBarShops.vue'
import Procurement from '@/Components/Navigation/TopBarMenu/TopBarProcurement.vue'
import Accounting from '@/Components/Navigation/TopBarMenu/TopBarAccounting.vue'
import Dashboard from '@/Components/Navigation/TopBarMenu/TopBarDashboard.vue'
import Dispatch from '@/Components/Navigation/TopBarMenu/TopBarDispatch.vue'
import Dropshipping from '@/Components/Navigation/TopBarMenu/TopBarDropshipping.vue'
import Fulfilment from '@/Components/Navigation/TopBarMenu/TopBarFulfilment.vue'
import Hr from '@/Components/Navigation/TopBarMenu/TopBarHr.vue'
import Inventory from '@/Components/Navigation/TopBarMenu/TopBarInventory.vue'
import Production from '@/Components/Navigation/TopBarMenu/TopBarProduction.vue'
import Sysadmin from '@/Components/Navigation/TopBarMenu/TopBarSysadmin.vue'

const props = defineProps<{
    tenantName: string
}>()

const currentUrl = ref()
router.on('navigate', (event) => {
	currentUrl.value = event.detail.page.url.split('/')[1]
})

const component = {
    'shops': Shops,
    'procurement': Procurement,
    'accounting': Accounting,
    'dashboard': Dashboard,
    'dispatch': Dispatch,
    'dropshipping': Dropshipping,
    'fulfilment': Fulfilment,
    'hr': Hr,
    'inventory': Inventory,
    'production': Production,
    'sysadmin': Sysadmin,
}
</script>

<style scoped></style>
