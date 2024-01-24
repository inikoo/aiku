<script setup lang="ts">
import { toRefs, watch, ref, onBeforeMount, Ref } from 'vue'
import { cloneDeep, get, isNull } from 'lodash'

const fallbackPath = '/fallback/fallback.svg'

const props = withDefaults(defineProps<{
    src?: {
        original: string
        original_2x?: string
        avif?: string,
        avif_2x?: string,
        webp?: string,
        webp_2x?: string,
    }
    imageCover?: boolean
    alt?: string,
    class?: string
}>(), {
    src: {
        original: fallbackPath
    }
})

const emits = defineEmits<{
    (e: 'onLoadImage'): void
}>()

const { src } = toRefs(props)


const imageSrc = ref(cloneDeep(src))
const avif: Ref<string | undefined> = ref(get(imageSrc, ['value', 'avif'], fallbackPath))
const webp: Ref<string | undefined> = ref(get(imageSrc, ['value', 'webp'], fallbackPath))
const original = ref(get(imageSrc, ['value', 'original'], fallbackPath))

watch(src, (newValue) => {
    if(!newValue) {
        
    }

    else {
        imageSrc.value = newValue
        avif.value = newValue.avif
        webp.value = newValue.webp
        original.value = newValue.original
        setImage()
    }
})

const setImage = () => {
    if(!isNull(imageSrc.value)){
        if (imageSrc.value?.avif_2x) {
            avif.value += ' 1x, ' + imageSrc.value.avif_2x + ' 2x'
        }

        if (imageSrc.value?.webp_2x) {
            webp.value += ' 1x, ' + imageSrc.value.webp_2x + ' 2x'
        }

        if (imageSrc.value?.original_2x) {
            original.value += ' 1x, ' + imageSrc.value.original_2x + ' 2x'
        }
    }
}

onBeforeMount(setImage)

</script>

<template>
    <picture :class="[props.class ?? 'w-full h-full flex justify-center items-center']">
        <!-- <source v-if="get(src, 'avif')" type="image/avif" :srcset="avif">
        <source v-if="get(src, 'webp')" type="image/webp" :srcset="webp"> -->
        <img :class="[imageCover ? 'w-full h-full object-cover' : '']" @load="() => emits('onLoadImage')" :srcset="original" :src="get(src, 'original')" :alt="alt" style="height: inherit;">
    </picture>
</template>
