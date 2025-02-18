<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

import { debounce } from "lodash-es"
import PureInput from '@/Components/Pure/PureInput.vue'
import LoadingText from '@/Components/Utils/LoadingText.vue'


onMounted(() => {
    // console.log('ppp', _pureInput.value?._inputRef)
    inputSearch.value = route().params.q
})

const luigiTrackerId = import.meta.env.VITE_LUIGI_TRACKER_ID
const luigiContent = import.meta.env.VITE_LUIGI_CONTENT_API
const inputSearch = ref('')
const _pureInput = ref()



</script>


<template>
    <div>
        <div class="mx-auto w-fit my-10">
            <PureInput
                ref="_pureInput"
                v-model="inputSearch"
                autofocus
                placeholder="Type your search"
                inputName="inputLuigi" />
        </div>

        <div class="mx-10 px-6 rounded-md ring-1 ring-gray-300">
            <div id="search-ui">
                <div class="text-3xl font-bold w-full flex justify-center py-8">
                    <LoadingText />
                </div>
            </div>
        </div>
    </div>
</template>

<style>


.luigi-ac-footer {
    visibility: hidden !important;
}
</style>