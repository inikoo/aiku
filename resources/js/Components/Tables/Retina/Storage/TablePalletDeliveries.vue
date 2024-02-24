<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 24 Feb 2024 13:06:04 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"

import { PalletDelivery } from "@/types/pallet-delivery";
import Icon from "@/Components/Icon.vue";

library.add(faPlus)

const props = defineProps<{
	data: object
	tab?: string
}>()

function palletDeliveryRoute(palletDelivery: PalletDelivery) {
    switch (route().current()) {
        default:
            return route(
                'retina.storage.pallet-deliveries.show',
                [
                    palletDelivery.slug
                ]);

    }
}



</script>

<template>
	<Table :resource="data" :name="tab" class="mt-5">


        <template #cell(reference)="{ item: palletDelivery }">
        <Link :href="palletDeliveryRoute(palletDelivery)" class="specialUnderline">
            {{ palletDelivery['reference'] }}
        </Link>
        </template>

        <template #cell(state)="{ item: palletDelivery }">
            <Icon :data="palletDelivery['state_icon']" class="px-1"/>
        </template>


		<template #buttondeliveries="{ linkButton: linkButton }">
			<Link
				v-if="linkButton?.route?.name"
				method="post"
				:href="route(linkButton?.route?.name, linkButton?.route?.parameters)"
				class="ring-1 ring-gray-300 overflow-hidden first:rounded-l last:rounded-r">
				<Button
					:style="linkButton.style"
					:label="linkButton.label"
					class="h-full capitalize inline-flex items-center rounded-none text-sm border-none font-medium shadow-sm focus:ring-transparent focus:ring-offset-transparent focus:ring-0">
				</Button>
			</Link>
		</template>
	</Table>
</template>
