<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-16 16:40
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { ref } from "vue"
import { faDolly } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Button from "@/Components/Elements/Buttons/Button.vue"
import AgentReceiveProductsDialog from "@/Components/Procurement/AgentReceiveProductsDialog.vue"
import PingIcon from "@/Components/Utils/PingIcon.vue"
import { ctrans } from "@/Composables/useTrans"

type ScopeOption = { value: number; label: string; count: number }

defineProps<{
	scopes: { suppliers: ScopeOption[]; purchase_orders: ScopeOption[] }
	purchaseOrderId?: number | null
	size?: string
	full?: boolean
	isNextStep?: boolean
}>()

const isOpen = ref(false)
</script>

<template>
	<span class="relative" :class="full ? 'block' : ''" v-tooltip="scopes.suppliers.length === 0 ? ctrans('Nothing is in production to receive') : isNextStep ? ctrans('Next step: book the goods the supplier delivered for this order') : ctrans('Book goods a supplier delivered: they become a goods receipt and move to Received')">
		<Button type="tertiary" :size="size" :full="full" :label="ctrans('Receive goods')" :disabled="scopes.suppliers.length === 0" @click="isOpen = true">
			<template #icon>
				<FontAwesomeIcon :icon="faDolly" class="text-green-600" fixed-width aria-hidden="true" />
			</template>
		</Button>
		<PingIcon v-if="isNextStep && scopes.suppliers.length" class="!absolute -right-1 -top-1 text-[7px] text-amber-500" aria-hidden="true" />
		<AgentReceiveProductsDialog v-model:visible="isOpen" :scopes="scopes" :purchaseOrderId="purchaseOrderId" />
	</span>
</template>
