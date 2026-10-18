<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { useForm } from "@inertiajs/vue3"
import Select from "primevue/select"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faExclamationTriangle } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import PureMultiselectInfiniteScroll from "@/Components/Pure/PureMultiselectInfiniteScroll.vue"
import { ctrans } from "@/Composables/useTrans"

const props = defineProps<{
    masterAssetId: number
    products: string[]
    masterShops: { id: number, slug: string, code: string, name: string, existing_codes: string[], blockers: string[] }[]
}>()

const masterShopId = ref<number | null>(props.masterShops.length === 1 ? props.masterShops[0].id : null)
const form = useForm<{ master_family_id: number | null }>({ master_family_id: null })

const masterShop = computed(() => props.masterShops.find((shop) => shop.id === masterShopId.value) ?? null)
const existingCodes = computed(() => masterShop.value?.existing_codes ?? [])
const blockers = computed(() => masterShop.value?.blockers ?? [])
const errors = computed(() => (Object.values(form.errors) as (string | string[])[]).flat())

watch(masterShopId, () => {
    form.master_family_id = null
    form.clearErrors()
})

const submit = () => {
    form.post(route("grp.models.master_asset.copy_to_master_shop", { masterAsset: props.masterAssetId }), {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="flex flex-col gap-4 text-sm">
        <p class="text-gray-600">
            {{ ctrans("Copies the master product with its trade units, texts, translations, prices and images. Nothing is created in any shop: add the copy to the shops afterwards.") }}
        </p>

        <div>
            <div class="mb-1 font-medium">{{ ctrans("Master shop") }}</div>
            <Select
                v-model="masterShopId"
                :options="masterShops"
                optionLabel="name"
                optionValue="id"
                :placeholder="ctrans('Select master shop')"
                fluid
            />
        </div>

        <div v-if="masterShop">
            <div class="mb-1 font-medium">{{ ctrans("Master family") }}</div>
            <PureMultiselectInfiniteScroll
                :key="masterShop.id"
                v-model="form.master_family_id"
                :fetchRoute="{ name: 'grp.json.master-family.all-master-family', parameters: { masterShop: masterShop.slug } }"
                :placeholder="ctrans('Select master family')"
                valueProp="id"
                labelProp="name"
                required
            >
                <template #singlelabel="{ value }">
                    <div class="w-full text-left pl-4">{{ value.code }} · {{ value.name }}</div>
                </template>
                <template #option="{ option }">
                    <div>{{ option.code }} · {{ option.name }}</div>
                </template>
            </PureMultiselectInfiniteScroll>
        </div>

        <div>
            <div class="mb-1 font-medium">
                {{ products.length > 1 ? ctrans("Master products copied (the whole variant)") : ctrans("Master product copied") }}
            </div>
            <div class="flex flex-wrap gap-1 max-h-40 overflow-y-auto">
                <span
                    v-for="code in products"
                    :key="code"
                    class="rounded border px-2 py-0.5 tabular-nums"
                    :class="existingCodes.includes(code) ? 'border-red-300 bg-red-50 text-red-700' : 'border-gray-200 text-gray-700'"
                >
                    {{ code }}
                </span>
            </div>
        </div>

        <div v-for="error in errors.length ? errors : blockers" :key="error" class="flex items-start gap-2 text-red-600">
            <FontAwesomeIcon :icon="faExclamationTriangle" class="mt-0.5" fixed-width />
            <span>{{ error }}</span>
        </div>

        <div class="flex justify-end">
            <Button
                type="tertiary"
                :label="ctrans('Copy')"
                :loading="form.processing"
                :disabled="!form.master_family_id || blockers.length > 0"
                @click="submit"
            >
                <template #icon>
                    <FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width />
                </template>
            </Button>
        </div>
    </div>
</template>
