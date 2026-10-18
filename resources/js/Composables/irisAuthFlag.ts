import { watch } from "vue"

const IRIS_AUTH_COOKIE = "iris_vua"

/* Storefront HTML comes from Varnish, so the first paint knows nothing about the visitor and
   is_logged_in used to be seeded only from the localStorage snapshot the previous first-hit wrote.
   That snapshot still says "logged out" on the page right after signing in, so the whole storefront
   painted its logged out chrome until the first-hit fetch landed seconds later. The backend sets
   iris_vua on login and forgets it on logout, and the browser already holds it on that first paint.
   The snapshot is not consulted: it outlives the session, so a visitor whose session expired painted
   logged in and then flipped to logged out when the first-hit answered (HELP-3928). */
export const hasIrisAuthCookie = (): boolean => {
    if (typeof document === "undefined") {
        return false
    }

    return document.cookie
        .split(";")
        .some(cookie => cookie.trim().split("=")[0] === IRIS_AUTH_COOKIE)
}

export const resolveIsLoggedIn = (): boolean => hasIrisAuthCookie()

export const whenIrisLoggedIn = (layout: { iris?: { is_logged_in?: boolean } }, callback: () => void): void => {
    let hasRun = false
    let stopWatching: (() => void) | undefined

    stopWatching = watch(
        () => layout?.iris?.is_logged_in,
        (isLoggedIn) => {
            if (!isLoggedIn || hasRun) {
                return
            }

            hasRun = true
            callback()
            stopWatching?.()
        },
        { immediate: true }
    )

    if (hasRun) {
        stopWatching()
    }
}
