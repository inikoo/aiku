<script setup lang="ts">
import PureTimeline from '@/Components/Pure/PureTimeline.vue'

import beePluginJsonExample from "@/Components/CMS/Website/Outboxes/Unlayer/beePluginJsonExample"
import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { Tabs as TSTabs } from '@/types/Tabs'
import { routeType } from '@/types/route'

import Timeline from '@/Components/Utils/Timeline.vue'
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faUser, faEnvelope, faSeedling, faShare, faInboxOut, faCheck, faEnvelopeOpen, faHandPointer, faUserSlash, faPaperPlane, faEyeSlash, faSkull, faDungeon } from '@fal';
library.add(faUser, faEnvelope, faSeedling, faShare, faInboxOut, faCheck, faEnvelopeOpen, faHandPointer, faUserSlash, faPaperPlane, faEyeSlash, faSkull, faDungeon)

const props = defineProps<{
    data : Object
}>()



console.log('sss',props)
</script>



<template>
    <div class="card p-4">
        <div class="col-span-2 w-full pb-4 border-b border-gray-300 mb-8">
            <div class="mt-4 sm:mt-0 border-b border-gray-200 pb-2">
                <Timeline :options="data.mailshot.data.timeline" :state="'sent'" :slidesPerView="6"  />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-8 gap-2">
            <!-- Data Grid -->
            <div class="md:col-span-8 grid sm:grid-cols-1 md:grid-cols-5 gap-2 h-auto mb-3">
                <div v-for="item in data.mailshot.data.stats" :key="item.label" :class="item.class"
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
                     <!--    <div class="text-sm text-white/80">Updated 5 minutes ago</div> -->
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
