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
import { ctrans } from "@/Composables/useTrans"

type ScopeOption = { value: number; label: string; count: number }

defineProps<{
	scopes: { suppliers: ScopeOption[]; purchase_orders: ScopeOption[] }
}>()

const isOpen = ref(false)
</script>

<template>
	<span v-tooltip="scopes.suppliers.length === 0 ? ctrans('Nothing is in production to receive') : ctrans('Book goods a supplier delivered: they become a goods receipt and move to Received')">
		<Button type="tertiary" :label="ctrans('Receive goods')" :disabled="scopes.suppliers.length === 0" @click="isOpen = true">
			<template #icon>
				<FontAwesomeIcon :icon="faDolly" class="text-green-600" fixed-width aria-hidden="true" />
			</template>
		</Button>
		<AgentReceiveProductsDialog v-model:visible="isOpen" :scopes="scopes" />
	</span>
</template>
