import assert from "node:assert/strict"
import { register } from "node:module"
import test from "node:test"

register(
	"data:text/javascript," +
		encodeURIComponent(`
			const resourcesJsUrl = ${JSON.stringify(new URL("../../resources/js/", import.meta.url).href)}
			export async function resolve(specifier, context, nextResolve) {
				if (specifier.startsWith("@/")) {
					return nextResolve(resourcesJsUrl + specifier.slice(2) + ".ts", context)
				}
				return nextResolve(specifier, context)
			}
		`)
)

const { parseStructuredData } = await import("../../resources/js/Iris/Composables/useStructuredData.ts")

test("webpages with no structured data write no JSON-LD at all", () => {
	for (const raw of [[], [""], "[]", '[""]', {}, "", null, "not json"]) {
		assert.equal(parseStructuredData(raw), null)
	}
})

test("every structured data node carries a schema context", () => {
	assert.deepEqual(parseStructuredData('{"@type":"Organization"}'), { "@context": "https://schema.org", "@type": "Organization" })
	assert.deepEqual(parseStructuredData(["", { "@context": "https://schema.org", "@type": "Service" }]), [{ "@context": "https://schema.org", "@type": "Service" }])
})

const { toJsonLd } = await import("../../resources/js/Iris/Composables/useStructuredData.ts")
const { buildWebpageStructuredData } = await import("../../resources/js/Iris/Composables/useWebpageStructuredData.ts")
const { buildBreadcrumbListNode } = await import("../../resources/js/Iris/Composables/useBreadcrumbStructuredData.ts")

test("JSON-LD written into the page HTML cannot close its own script tag", () => {
	assert.equal(toJsonLd({ name: "</script><script>alert(1)</script>" }), '{"name":"\\u003c/script>\\u003cscript>alert(1)\\u003c/script>"}')
})

test("relative breadcrumb links resolve against the page URL, so the server writes the same absolute links the browser does", () => {
	const breadcrumbs = [{ type: "simple", simple: { url: "/" } }, { type: "simple", simple: { label: "Aromatherapy", url: "https://www.aw-dropship.com/aromatherapy" } }]

	assert.deepEqual(
		buildBreadcrumbListNode(breadcrumbs, "https://www.aw-dropship.com/aromatherapy/aweo")?.itemListElement.map((item: Record<string, unknown>) => item.item),
		["https://www.aw-dropship.com/", "https://www.aw-dropship.com/aromatherapy"]
	)
})

const disclosureBlock = (isFaq: boolean) => ({
	type: "disclosure",
	web_block: { layout: { data: { fieldValue: { id: "faq", seo: { is_faq: isFaq }, value: [{ label: "Is it vegan?", description: "Yes" }] } } } },
})

const subDepartmentsBlock = (id: string) => ({
	type: "sub-departments-1",
	web_block: { layout: { data: { fieldValue: { id, sub_departments: [{ name: "Oils", url: "https://www.aw-dropship.com/aromatherapy/oils" }] } } } },
})

test("a webpage writes an FAQ only for disclosures marked as FAQ", () => {
	const webpage_data = { canonical_url: "https://www.aw-dropship.com/faq" }

	assert.equal(buildWebpageStructuredData({ webpage_data, web_blocks: [disclosureBlock(false)] }).length, 0)
	assert.equal((buildWebpageStructuredData({ webpage_data, web_blocks: [disclosureBlock(true)] })[0] as Record<string, unknown>)["@type"], "FAQPage")
})

test("a webpage writes one category item list even when it has several category blocks", () => {
	const webpage_data = { canonical_url: "https://www.aw-dropship.com/aromatherapy", model_type: "ProductCategory", sub_type: "department" }

	assert.equal(buildWebpageStructuredData({ webpage_data, web_blocks: [subDepartmentsBlock("a"), subDepartmentsBlock("b")] }).length, 1)
})

test("a webpage whose blocks arrive keyed by position still writes their structured data", () => {
	const webpage_data = { canonical_url: "https://www.aw-dropship.com/faq" }

	assert.equal(buildWebpageStructuredData({ webpage_data, web_blocks: { 0: disclosureBlock(true) } }).length, 1)
})

test("building a webpage's structured data leaves the page props it reads untouched", () => {
	const webpage_data = {
		canonical_url: "https://www.aw-dropship.com/aromatherapy/eo-20",
		seo_data: { structured_data: { "@graph": [{ "@type": "Product", name: "Patchouli" }] } },
	}
	const web_blocks = [{ type: "product-1", web_block: { layout: { data: { fieldValue: { product: { id: 1, name: "Patchouli", code: "EO-20", price: 3.2, stock: 4 } } } } } }]
	const before = JSON.stringify({ webpage_data, web_blocks })

	buildWebpageStructuredData({ webpage_data, web_blocks, currency_code: "GBP" })

	assert.equal(JSON.stringify({ webpage_data, web_blocks }), before)
})
