<script setup lang='ts'>
import { useRangeFromNow } from '@/Composables/useFormatTime'
import SliderLandscape from "@/Components/Banners/Slider/SliderLandscape.vue"
import SliderSquare from "@/Components/Banners/Slider/SliderSquare.vue"
import Image from '@/Components/Image.vue'

const props = defineProps<{
    data: {
        type: string
        compiled_layout: {}
        published_snapshot: {
            publisher: string
            publisher_avatar: string
            comment: string
            published_at: string
        }
    }
}>()

</script>

<template>
    <!-- If banner is 'landscape' -->
    <div v-if="data.type == 'landscape'">
        <div v-if="data.published_snapshot" class="w-full bg-white flex items-center justify-between py-3 px-4">
            <div class="flex gap-x-2">
                <div class="h-5 aspect-square rounded-full overflow-hidden ring-1 ring-gray-300">
                    <Image :src="data.published_snapshot.publisher_avatar" />
                </div>
                <div class="font-bold text-lg leading-none">{{ data.published_snapshot.publisher }}</div>
                <div v-if="data.published_snapshot.comment" class="text-sm text-gray-500 italic">
                    ({{ data.published_snapshot.comment }})
                </div>
            </div>
            <div v-if="data.published_snapshot.published_at" class="text-sm text-gray-600 tracking-wide text-right">
                Published at <span class="font-bold">{{ useRangeFromNow(data.published_snapshot.published_at) }}</span> ago
            </div>
        </div>
        <div class="aspect-[2/1] md:aspect-[3/1] lg:aspect-[4/1] w-fit h-56 md:h-60">
            <SliderLandscape :data="data.compiled_layout" :production="true" />
        </div>
    </div>

    <!-- If banner is 'square' -->
    <div v-else class="">
        <div v-if="data.published_snapshot" class="w-full bg-white flex items-center justify-between py-3 px-4">
            <div class="flex gap-x-2">
                <div class="h-5 aspect-square rounded-full overflow-hidden ring-1 ring-gray-300">
                    <Image :src="data.published_snapshot.publisher_avatar" />
                </div>
                <div class="font-bold text-lg leading-none">{{ data.published_snapshot.publisher }}</div>
                <div v-if="data.published_snapshot.comment" class="text-sm text-gray-500 italic">
                    ({{ data.published_snapshot.comment }})
                </div>
            </div>
            <div v-if="data.published_snapshot.published_at" class="text-sm text-gray-600 tracking-wide text-right">
                Published at <span class="font-bold">{{ useRangeFromNow(data.published_snapshot.published_at) }}</span> ago
            </div>
        </div>
        <div class="aspect-[2/1] md:aspect-[3/1] lg:aspect-[4/1] w-fit h-56 md:h-60">
            <SliderSquare :data="data.compiled_layout" :production="true" />
        </div>
    </div>
    <!-- <pre>{{ data.compiled_layout }}</pre> -->
</template>
