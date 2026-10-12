<script setup lang="ts">
import { computed, ref } from "vue"
import LinkIris from "@/Iris/Components/LinkIris.vue"
import { ctrans } from "@/Composables/useTrans"

const props = withDefaults(defineProps<{
    components: Array<{
        code: string
        name: string
        quantity: number
        url: string | null
        ingredients: string | null
    }>
    collapsedCount?: number
}>(), {
    collapsedCount: 8
})

const isExpanded = ref(false)

const isCollapsible = computed(() => props.components.length > props.collapsedCount + 2)

const visibleComponents = computed(() =>
    isCollapsible.value && !isExpanded.value
        ? props.components.slice(0, props.collapsedCount)
        : props.components
)
</script>

<template>
    <div class="product-components">
        <ul class="product-components-list">
            <li v-for="component in visibleComponents" :key="component.code" class="product-components-item">
                <span class="product-components-quantity">{{ component.quantity }}x</span>
                <span class="product-components-name">
                    <LinkIris v-if="component.url" :href="component.url" class="product-components-link">
                        {{ component.name }}
                    </LinkIris>
                    <span v-else>{{ component.name }}</span>
                    <span v-if="component.ingredients" class="product-components-ingredients">
                        {{ component.ingredients }}
                    </span>
                </span>
            </li>
        </ul>

        <button v-if="isCollapsible" type="button" class="product-components-toggle" @click="isExpanded = !isExpanded">
            {{ isExpanded
                ? ctrans("Show Less")
                : ctrans("Show all :count products", { count: String(components.length) }) }}
        </button>
    </div>
</template>

<style scoped>
.product-components {
    white-space: normal;
    text-align: left;
}

.product-components-list {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.product-components-item {
    display: flex;
    gap: 0.5rem;
    align-items: baseline;
}

.product-components-quantity {
    flex: none;
    min-width: 1.75rem;
    font-variant-numeric: tabular-nums;
    opacity: 0.6;
}

.product-components-name {
    min-width: 0;
}

.product-components-name :deep(.product-components-link) {
    text-decoration: underline;
    text-underline-offset: 2px;
}

.product-components-name :deep(.product-components-link:hover) {
    color: var(--theme-color-4);
}

.product-components-ingredients {
    display: block;
    font-size: 0.75rem;
    line-height: 1rem;
    opacity: 0.7;
}

.product-components-toggle {
    margin-top: 0.5rem;
    font-size: 0.75rem;
    text-decoration: underline;
}
</style>
