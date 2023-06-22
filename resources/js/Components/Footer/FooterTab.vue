<template>
    <div class="absolute bottom-6 right-0 w-40 min-w-min overflow-hidden rounded-t">
        <div class="w-full bg-gray-800 shadow-lg flex-row items-start text-gray-100 text-[11px] leading-none" >
            <div class="flex flex-col justify-center text-center p-1 pb-3 gap-y-1">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
</script>

<style lang="scss" scoped></style>