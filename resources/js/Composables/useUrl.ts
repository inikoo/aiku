import { usePage } from "@inertiajs/vue3"

// from "http://app.aiku.test/org/sk/inventory" to "/org/sk/inventory"
export const removeDomain = (fullUrl: string, domain: string) => {
    if(!fullUrl) return ''

    const domainRegex = new RegExp(`https?://${domain}`, 'i')

    return fullUrl.replace(domainRegex, '')
}

// Check if current route is same as the given route
export const isRouteSameAsCurrentUrl = (expectedRoute: string) => {
    return usePage().url.includes(removeDomain(expectedRoute, route().v().route.domain))
}

// routeRoot: the route.root to indicates a group of Navigation ('grp.org.fulfilments.show.operations.pallets.index' is exist in 'grp.org.fulfilments.show.operations.')
export const isNavigationActive = (layoutRoute: string, routeRoot: string | undefined) => {
    if(!routeRoot) return false

    return layoutRoute.includes(routeRoot)
}