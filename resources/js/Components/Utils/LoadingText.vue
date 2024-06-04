<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { trans } from 'laravel-vue-i18n'

const loadingText = ref('')
const dots = ref(0)

const updateLoadingText = () => {
    dots.value = (dots.value + 1) % 4
    loadingText.value = '.'.repeat(dots.value)
}

let intervalId: ReturnType<typeof setTimeout> | null = null

onMounted(() => {
    intervalId = setInterval(updateLoadingText, 400)
})

onUnmounted(() => {
    if (intervalId !== null) {
        clearInterval(intervalId)
    }
})
</script>

<template>
    <div class="relative w-fit">
        <span>{{ trans('Loading') }}.</span>
        <div class="absolute bottom-0 left-full">{{ loadingText }}</div>
    </div>
</template>