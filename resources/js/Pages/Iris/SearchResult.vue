<script setup lang='ts'>
import { onMounted } from 'vue'
import LoadingText from '@/Components/Utils/LoadingText.vue'

const luigiTrackerId = import.meta.env.VITE_LUIGI_TRACKER_ID
const defaultTrackerId = '179075-204259'
    
// const listFieldsRemoved = ['price', 'formatted_price', 'price_amount']
const listFieldsRemoved = ['price', 'price_amount']

</script>

<template>
    <div>
        <div class="relative pt-10 px-7 h-full overflow-y-auto">
            <div id="search-ui">
                <div class="text-3xl font-bold w-full flex justify-center py-8">
                    <LoadingText />
                </div>
            </div>
        </div>
    </div>
</template>