<script setup lang="ts">
import { faCrown, faStarChristmas } from "@fas"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { getStyles } from "@/Composables/styles"
import { ctrans } from "@/Composables/useTrans"
import { resolveProductBadge, type ProductWithBadge } from "@/Composables/useProductBadge"
import { computed } from "vue"
import { get } from "lodash-es"

const props = defineProps<{
    product?: ProductWithBadge
    data?: any
    screenType?: "mobile" | "tablet" | "desktop"
    bestsellerNeedsStock?: boolean
}>()

const badges = {
    new_in: {
        settingsKey: "new_in",
        icon: faStarChristmas,
        label: () => ctrans("New In"),
        background: "#EE7B2B",
        border: "1px solid #EE7B2B",
        color: "#FFFFFF",
        iconColor: "#FFFFFF",
    },
    back_in_stock: {
        settingsKey: "back_in_stock",
        icon: faStarChristmas,
        label: () => ctrans("Back In Stock"),
        background: "#FFFFFF",
        border: "1px solid #1F2937",
        color: "#1F2937",
        iconColor: "#1F2937",
    },
    bestseller: {
        settingsKey: "bestseller1",
        icon: faCrown,
        label: () => ctrans("Bestseller"),
        background: "#DDB862",
        border: "1px solid #DDB862",
        color: "#1F2937",
        iconColor: "#8A6A1F",
    },
}

const badge = computed(() => resolveProductBadge(props.product, props.bestsellerNeedsStock))

const config = computed(() => (badge.value ? badges[badge.value] : null))
const settings = computed(() => get(props.data, [config.value?.settingsKey ?? ""]))

const useIcon = computed(() => get(settings.value, ["icon", "use_icon"], true))
const showText = computed(() => props.screenType !== "mobile" || !useIcon.value)

const containerStyle = computed(() => {
    const layout = getStyles(settings.value?.container?.properties, props.screenType, false)

    return {
        background: layout?.background || config.value?.background,
        borderTop: layout?.borderTop || config.value?.border,
        borderBottom: layout?.borderBottom || config.value?.border,
        borderLeft: layout?.borderLeft || config.value?.border,
        borderRight: layout?.borderRight || config.value?.border,
        borderTopLeftRadius: layout?.borderTopLeftRadius || "0.375rem",
        borderTopRightRadius: layout?.borderTopRightRadius || "0.375rem",
        borderBottomLeftRadius: layout?.borderBottomLeftRadius || "0.375rem",
        borderBottomRightRadius: layout?.borderBottomRightRadius || "0.375rem",
        color: config.value?.color,
    }
})

const iconStyle = computed(() => {
    const icon = getStyles(settings.value?.icon, props.screenType, false)

    return {
        color: icon?.color || config.value?.iconColor,
        fontFamily: icon?.fontFamily,
        fontSize: icon?.fontSize,
        fontStyle: icon?.fontStyle,
    }
})

const textStyle = computed(() => {
    const text = getStyles(settings.value?.text?.properties, props.screenType, false)

    return {
        color: text?.color || config.value?.color,
        fontFamily: text?.fontFamily,
        fontSize: text?.fontSize,
        fontStyle: text?.fontStyle,
    }
})
</script>

<template>
    <div v-if="badge && config" class="product-badge" :class="`product-badge-${badge}`" :style="containerStyle">
        <FontAwesomeIcon v-if="useIcon" :icon="config.icon" class="product-badge-icon" :style="iconStyle" fixed-width />
        <span v-if="useIcon && showText" class="product-badge-divider" />
        <span v-if="showText" :style="textStyle">{{ config.label() }}</span>
    </div>
</template>

<style scoped>
.product-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    z-index: 10;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.125rem 0.5rem;
    font-size: 0.75rem;
    line-height: 1rem;
    font-weight: 700;
    white-space: nowrap;
}

.product-badge-icon {
    width: 0.875rem;
    height: 0.875rem;
}

.product-badge-divider {
    align-self: stretch;
    width: 1px;
    background: currentColor;
    opacity: 0.6;
}
</style>
