/*
 * Author Louis Perez
 * Created on 16-10-2026-13h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

import { onBeforeUnmount, onMounted, ref, type Ref } from "vue"

export function useFitToViewport(element: Ref<HTMLElement | null>, bottomPadding = 24, minimumHeight = 320, mediaQuery = "(min-width: 1024px)") {
    const height = ref<string | undefined>(undefined)

    const fit = () => {
        if (!element.value || !window.matchMedia(mediaQuery).matches) {
            height.value = undefined

            return
        }
        const top = element.value.getBoundingClientRect().top + window.scrollY
        height.value = `${Math.max(window.innerHeight - top - bottomPadding, minimumHeight)}px`
    }

    onMounted(() => {
        fit()
        window.addEventListener("resize", fit)
    })

    onBeforeUnmount(() => window.removeEventListener("resize", fit))

    return { height, fit }
}
