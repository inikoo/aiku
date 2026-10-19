<script setup lang="ts">
import { ctrans } from "@/Composables/useTrans"

defineProps<{
    environment?: string
}>()
</script>

<template>
    <div v-if="environment === 'sandbox'" class="mb-3 text-left font-normal">
        <div class="mb-2 rounded border-2 border-red-500 bg-red-50 px-4 py-3 text-center text-lg font-bold uppercase text-red-700">
            {{ ctrans("Test site: do not use a real credit or debit card") }}
        </div>
        <div class="rounded border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900">
            <div class="font-semibold">{{ ctrans("Card payments here are simulated, use this test card") }}</div>
            <div>{{ ctrans("Card") }}: <span class="font-mono whitespace-nowrap">5385 3083 6013 5181</span></div>
            <div>{{ ctrans("Expiry: any future date, CVV: any 3 digits") }}</div>
            <div>{{ ctrans("3D Secure password") }}: <span class="font-mono">Checkout1!</span></div>
        </div>
    </div>
</template>
