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