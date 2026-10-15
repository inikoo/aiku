<script setup lang="ts">
import { ref } from "vue"
import { router } from "@inertiajs/vue3"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faRobot } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { TradeUnit } from "@/types/trade-unit"
library.add(faRobot)

const props = defineProps<{
    tradeUnits: TradeUnit[]
}>()

const isLoading = ref(false)

const translateNames = () => {
    router.post(
        route("grp.models.trade_units.bulk_translate_names"),
        { trade_units: props.tradeUnits.map((tradeUnit) => tradeUnit.id) },
        {
            preserveScroll: true,
            onStart: () => { isLoading.value = true },
            onFinish: () => { isLoading.value = false },
        }
    )
}
</script>

<template>
    <Button
        v-tooltip="tradeUnits.length ? ctrans('Adds the missing names in every shop language, names already there are kept') : ctrans('Select trade units in the table first')"
        type="tertiary"
        :label="tradeUnits.length ? `${ctrans('Translate names')} (${tradeUnits.length})` : ctrans('Translate names')"
        :disabled="!tradeUnits.length"
        :loading="isLoading"
        @click="translateNames"
    >
        <template #icon>
            <FontAwesomeIcon icon="fal fa-robot" class="text-[--app-accent]" fixed-width aria-hidden="true" />
        </template>
    </Button>
</template>
