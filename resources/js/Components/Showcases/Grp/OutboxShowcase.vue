<script setup lang="ts">
import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { Tabs as TSTabs } from '@/types/Tabs'

import { library } from "@fortawesome/fontawesome-svg-core";
import { faInboxOut } from "@fal";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faUser } from '@fas';
library.add(faInboxOut)

const props = defineProps<{
   data : any
}>()




console.log('ddf',props.data)

</script>



<template>
    <div class="card p-4">
        <div class="grid grid-cols-1 md:grid-cols-8 gap-4">
            <div class="md:col-span-8 grid sm:grid-cols-1 md:grid-cols-6 gap-4 h-auto mb-3">
                <div v-for="item in data.stats" :key="item.key" :class="item.class"
                    class="bg-gradient-to-tr text-white flex flex-col justify-between px-6 py-2 rounded-lg shadow-lg sm:h-auto">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <div class="text-lg font-semibold capitalize">{{ item.label }}</div>
                        </div>
                        <div class="rounded-full bg-white/20 p-2">
                            <FontAwesomeIcon :icon="item.icon" class="text-xl" />
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ item.value }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>



<style lang="scss" scoped>
.card {
    padding: 1rem;
    border-radius: 8px;

    @media (max-width: 768px) {
        padding: 0.5rem;
    }
}

.grid-cols-7 {
    display: grid;
    grid-template-columns: repeat(7, 1fr);

    @media (max-width: 768px) {
        grid-template-columns: repeat(2, 1fr);
    }
}

.text-xl {
    font-size: 1.25rem;

    @media (max-width: 640px) {
        font-size: 1rem;
    }
}
</style>
