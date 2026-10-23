import {
    normalizeUrl,
    stripHtml,
} from "@/Iris/Composables/useStructuredData"

type StructuredDataNode = Record<string, any>

// Node: BreadcrumbList (kept independent from the page structured data so it can be
// rendered directly by BreadcrumbsIris.vue on every page that shows breadcrumbs)
export const buildBreadcrumbListNode = (
    breadcrumbs: any[] | null | undefined,
    baseUrl?: string | null
): StructuredDataNode | null => {
    if (!Array.isArray(breadcrumbs) || !breadcrumbs.length) return null

    const itemListElement = breadcrumbs
        .map((breadcrumb, index) => {
            if (breadcrumb?.type !== "simple") return null

            const name = stripHtml(breadcrumb?.simple?.label) ?? (index === 0 ? "Home" : undefined)
            if (!name) return null

            const item = normalizeUrl(breadcrumb?.simple?.url, baseUrl)

            const listItem: StructuredDataNode = {
                "@type": "ListItem",
                position: index + 1,
                name,
            }

            if (item) {
                listItem.item = item
            }

            return listItem
        })
        .filter((item): item is StructuredDataNode => item !== null)

    if (!itemListElement.length) return null

    return {
        "@type": "BreadcrumbList",
        itemListElement,
    }
}
