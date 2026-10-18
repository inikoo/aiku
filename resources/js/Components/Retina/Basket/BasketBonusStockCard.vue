<script setup lang="ts">
import { computed, inject } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faGift, faImage, faLock } from '@fal'
import { faCheckCircle } from '@fas'
import { ctrans } from '@/Composables/useTrans'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import { retinaLayoutStructure } from '@/Composables/useRetinaLayoutStructure'
import Image from '@common/Components/Image.vue'
import { BasketBonusStock } from '@/types/Iris/BasketBonusStock'

const props = defineProps<{
    bonusStock: BasketBonusStock
}>()

const locale = inject('locale', aikuLocaleStructure)
const layout = inject('layout', retinaLayoutStructure)

const firstProduct = computed(() => props.bonusStock.products[0] ?? null)
const familyLabel = computed(() => props.bonusStock.family_name || props.bonusStock.family_code || '')
const missingQuantity = computed(() => Math.max(0, props.bonusStock.target - props.bonusStock.current))

const progressPercentage = computed(() => {
    if (!props.bonusStock.target) {
        return 100
    }

    return Math.min(100, props.bonusStock.current / props.bonusStock.target * 100)
})
</script>

<template>
    <div class="bonus-card" :class="{ 'is-reached': bonusStock.is_reached }">
        <div class="bonus-card__product">
            <div class="bonus-card__thumb">
                <Image v-if="firstProduct?.web_image_thumbnail" :src="firstProduct.web_image_thumbnail" />
                <FontAwesomeIcon v-else :icon="faImage" class="bonus-card__placeholder" fixed-width aria-hidden="true" />
            </div>

            <div class="bonus-card__details">
                <div class="bonus-card__title">
                    <FontAwesomeIcon :icon="faGift" fixed-width aria-hidden="true" />
                    <span><strong>{{ ctrans('Gift') }}:</strong> {{ ctrans('Free :family stock', { family: familyLabel }) }}</span>
                </div>

                <template v-if="firstProduct">
                    <div class="bonus-card__name">
                        {{ firstProduct.name }}
                        <span v-if="bonusStock.products.length > 1" class="bonus-card__more">
                            {{ ctrans('+:count more', { count: bonusStock.products.length - 1 }) }}
                        </span>
                    </div>
                    <div class="bonus-card__code">
                        {{ firstProduct.code }}
                        <template v-if="Number(firstProduct.rrp) > 0">
                            <span class="bonus-card__separator">|</span>
                            {{ ctrans('RRP :amount', { amount: locale.currencyFormat(layout.iris?.currency?.code, Number(firstProduct.rrp)) }) }}
                        </template>
                    </div>
                </template>

                <span class="bonus-card__pill">
                    <FontAwesomeIcon :icon="bonusStock.is_reached ? faCheckCircle : faLock" fixed-width aria-hidden="true" />
                    {{ bonusStock.is_reached
                        ? ctrans(':quantity x free unlocked', { quantity: bonusStock.quantity })
                        : ctrans('Unlock at :target items', { target: locale.number(bonusStock.target) }) }}
                </span>
            </div>
        </div>

        <div class="bonus-card__progress">
            <div class="bonus-card__track">
                <div class="bonus-card__fill" :style="{ width: progressPercentage + '%' }" />
            </div>

            <div class="bonus-card__count">
                <strong>{{ locale.number(bonusStock.current) }}</strong>
                <span class="bonus-card__target"> / {{ ctrans(':target items', { target: locale.number(bonusStock.target) }) }}</span>
            </div>

            <div class="bonus-card__hint">
                <template v-if="bonusStock.is_reached">
                    {{ ctrans(':quantity free will be added to your order when you place it.', { quantity: bonusStock.quantity }) }}
                </template>
                <template v-else>
                    {{ ctrans('Add :missing more :family items to unlock :quantity free.', { missing: locale.number(missingQuantity), family: familyLabel, quantity: bonusStock.quantity }) }}
                </template>
            </div>

            <div class="bonus-card__note">
                {{ ctrans('Free stock is added automatically when you place the order. While stocks last.') }}
            </div>
        </div>
    </div>
</template>

<style scoped>
.bonus-card {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem 1rem;
    padding: 0.875rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: #f9fafb;
    transition: background-color 0.5s, border-color 0.5s;
}

.bonus-card.is-reached {
    border-color: #bbf7d0;
    background: #f0fdf4;
}

.bonus-card__product {
    display: flex;
    flex: 1 1 16rem;
    gap: 0.875rem;
    min-width: 0;
}

.bonus-card__thumb {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    width: 4.5rem;
    height: 4.5rem;
    overflow: hidden;
    border-radius: 0.375rem;
    background: #fff;
}

.bonus-card__thumb :deep(picture),
.bonus-card__thumb :deep(img) {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.bonus-card__placeholder {
    font-size: 1.5rem;
    opacity: 0.4;
}

.bonus-card__details {
    min-width: 0;
    font-size: 0.75rem;
    line-height: 1.125rem;
}

.bonus-card__title {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #111827;
}

.bonus-card__name {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 600;
    color: #1f2937;
}

.bonus-card__more {
    font-weight: 400;
    color: #9ca3af;
}

.bonus-card__code {
    color: #6b7280;
}

.bonus-card__separator {
    margin: 0 0.25rem;
    color: #d1d5db;
}

.bonus-card__pill {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    margin-top: 0.5rem;
    padding: 0.125rem 0.625rem;
    border-radius: 9999px;
    font-weight: 500;
    color: #4b5563;
    background: #e5e7eb;
}

.is-reached .bonus-card__pill {
    color: #15803d;
    background: #dcfce7;
}

.bonus-card__progress {
    flex: 1 1 14rem;
    min-width: 0;
    padding-left: 1rem;
    border-left: 1px solid #e5e7eb;
    font-size: 0.75rem;
    line-height: 1.125rem;
}

.bonus-card__track {
    position: relative;
    height: 0.5rem;
    margin: 0.25rem 0 0.5rem;
    overflow: hidden;
    border-radius: 9999px;
    background: #e5e7eb;
}

.bonus-card__fill {
    position: absolute;
    inset: 0 auto 0 0;
    border-radius: 9999px;
    background: #22c55e;
    transition: width 1s ease-in-out;
}

.bonus-card__count {
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #111827;
    font-variant-numeric: tabular-nums;
}

.bonus-card__target {
    color: #6b7280;
}

.bonus-card__hint {
    margin-top: 0.25rem;
    color: #374151;
}

.is-reached .bonus-card__hint {
    color: #15803d;
}

.bonus-card__note {
    margin-top: 0.375rem;
    font-size: 0.6875rem;
    line-height: 1rem;
    color: #9ca3af;
}

@media (max-width: 639px) {
    .bonus-card__progress {
        padding-left: 0;
        border-left: 0;
    }
}
</style>
