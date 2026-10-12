<script setup lang="ts">
import { computed, inject } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faGift, faImage } from '@fal'
import { faCheckCircle } from '@fas'
import { ctrans } from '@/Composables/useTrans'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import Image from '@common/Components/Image.vue'
import { BasketBonusStock } from '@/types/Iris/BasketBonusStock'

const props = defineProps<{
    bonusStock: BasketBonusStock
}>()

const locale = inject('locale', aikuLocaleStructure)

const firstProduct = computed(() => props.bonusStock.products[0] ?? null)

const progressPercentage = computed(() => {
    if (!props.bonusStock.target) {
        return 100
    }

    return Math.min(100, props.bonusStock.current / props.bonusStock.target * 100)
})

const progressHint = computed(() => {
    if (props.bonusStock.is_reached) {
        return ctrans(':quantity free added to your basket', { quantity: props.bonusStock.quantity })
    }

    return ctrans('Add :missing more items from this range to get :quantity free', {
        missing: locale.number(props.bonusStock.target - props.bonusStock.current),
        quantity: props.bonusStock.quantity,
    })
})
</script>

<template>
    <div v-tooltip="progressHint" class="bonus-meter" :class="{ 'is-reached': bonusStock.is_reached }">
        <div class="bonus-meter__thumb">
            <Image v-if="firstProduct?.web_image_thumbnail" :src="firstProduct.web_image_thumbnail" />
            <FontAwesomeIcon v-else :icon="faImage" class="bonus-meter__placeholder" fixed-width aria-hidden="true" />
        </div>

        <div class="bonus-meter__body">
            <div class="bonus-meter__title">
                <FontAwesomeIcon :icon="faGift" fixed-width aria-hidden="true" />
                <span>{{ ctrans('Bonus Stock') }}</span>
            </div>
            <div class="bonus-meter__name">{{ firstProduct?.name ?? bonusStock.label }}</div>
        </div>

        <div class="bonus-meter__progress">
            <div class="bonus-meter__track">
                <div class="bonus-meter__fill" :style="{ width: progressPercentage + '%' }" />
            </div>
            <div class="bonus-meter__count">
                <span class="bonus-meter__current">{{ locale.number(bonusStock.current) }}</span>
                <span class="bonus-meter__target"> / {{ ctrans(':target items', { target: locale.number(bonusStock.target) }) }}</span>
            </div>
        </div>

        <FontAwesomeIcon v-if="bonusStock.is_reached" :icon="faCheckCircle" class="bonus-meter__check" fixed-width aria-hidden="true" />
    </div>
</template>

<style scoped>
.bonus-meter {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.625rem 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: #f3f4f6;
    transition: background-color 0.5s, border-color 0.5s;
}

.bonus-meter.is-reached {
    border-color: #bbf7d0;
    background: #f0fdf4;
}

.bonus-meter__thumb {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    width: 3.25rem;
    height: 3.25rem;
    overflow: hidden;
    border-radius: 0.375rem;
    background: #fff;
}

.bonus-meter__thumb :deep(picture),
.bonus-meter__thumb :deep(img) {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.bonus-meter__placeholder {
    font-size: 1.25rem;
    opacity: 0.4;
}

.bonus-meter__body {
    flex: 1 1 0%;
    min-width: 0;
}

.bonus-meter__title {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.25rem;
    color: #111827;
}

.bonus-meter__name {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.75rem;
    line-height: 1.125rem;
    color: #4b5563;
}

.bonus-meter__progress {
    flex: 1 1 0%;
    min-width: 0;
    padding-left: 0.75rem;
    border-left: 1px solid #d1d5db;
}

.is-reached .bonus-meter__progress {
    border-left-color: #bbf7d0;
}

.bonus-meter__track {
    position: relative;
    height: 0.5rem;
    overflow: hidden;
    border-radius: 9999px;
    background: #d1d5db;
}

.bonus-meter__fill {
    position: absolute;
    inset: 0 auto 0 0;
    border-radius: 9999px;
    background: #9ca3af;
    transition: width 1s ease-in-out;
}

.is-reached .bonus-meter__fill {
    background: #22c55e;
}

.bonus-meter__count {
    margin-top: 0.375rem;
    font-size: 0.75rem;
    line-height: 1rem;
    color: #111827;
    font-variant-numeric: tabular-nums;
}

.is-reached .bonus-meter__current {
    font-weight: 600;
}

.bonus-meter__target {
    color: #9ca3af;
}

.bonus-meter__check {
    flex-shrink: 0;
    font-size: 1.5rem;
    color: #22c55e;
}
</style>
