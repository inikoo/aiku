<script setup lang="ts">
import { ref, onMounted, onUnmounted, defineExpose } from 'vue';

const showPopup = ref(false);
const customDiv = ref(null);
const contentWidth = ref('200px'); // Default width

const toggle = () => {
    showPopup.value = !showPopup.value;
};

const handleClickOutside = (event) => {
    if (customDiv.value && !customDiv.value.contains(event.target)) {
        showPopup.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

defineExpose({
    toggle,
    showPopup,
    contentWidth,
});
</script>

<template>
    <div ref="customDiv" class="relative">
        <div>
            <slot name="header" :data="{ toggle: toggle }" />
        </div>

        <transition name="fade">
            <div v-if="showPopup" class="absolute z-50 mt-2 bg-white shadow-lg rounded-md overflow-hidden"
                :style="{ width: contentWidth }" @click.stop @contextmenu.stop>
                <div class="p-4">
                    <slot name="content" />
                </div>
            </div>
        </transition>
    </div>
</template>


<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter,
.fade-leave-to

/* .fade-leave-active in <2.1.8 */
    {
    opacity: 0;
}
</style>