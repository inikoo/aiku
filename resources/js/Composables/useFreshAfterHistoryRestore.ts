import { ref, onMounted } from "vue"
import { router } from "@inertiajs/vue3"

let restoredFromHistory = (globalThis.performance?.getEntriesByType?.("navigation")[0] as PerformanceNavigationTiming | undefined)?.type === "back_forward"
globalThis.window?.addEventListener("popstate", () => (restoredFromHistory = true))
router.on("start", () => (restoredFromHistory = false))

export const useFreshAfterHistoryRestore = (only: string[], onRefreshed: () => void) => {
    const isRefreshing = ref(restoredFromHistory)
    restoredFromHistory = false

    onMounted(() => {
        if (!isRefreshing.value) {
            return
        }

        router.reload({
            only,
            onSuccess: () => {
                onRefreshed()
                isRefreshing.value = false
            },
            onFinish: () => {
                if (isRefreshing.value) {
                    window.location.reload()
                }
            },
        })
    })

    return isRefreshing
}
