import assert from "node:assert/strict"
import test from "node:test"
import {
	applyLinkColor,
	createRenderContext,
	isLinkUnderlined,
	renderModuleHtml,
} from "../../resources/js/Components/CMS/Website/Outboxes/EmailWorkshop/renderEmailHtml.ts"
import {
	MODULE_TYPES,
	createModule,
} from "../../resources/js/Components/CMS/Website/Outboxes/EmailWorkshop/emailWorkshopBlocks.ts"

const emailWithBody = (computedStyle: Record<string, unknown> = {}) => ({
	page: {
		rows: [],
		body: { container: { style: {} }, content: { style: {}, computedStyle: { linkColor: "#0068A5", ...computedStyle } } },
	},
}) as any

test("links are underlined unless the block or the email turns it off", () => {
	assert.equal(isLinkUnderlined(undefined, undefined), true)
	assert.equal(isLinkUnderlined(undefined, false), false)
	assert.equal(isLinkUnderlined(true, false), true)
	assert.equal(isLinkUnderlined(false, true), false)
})

test("a link without its own style gets the link colour and the chosen underline", () => {
	assert.equal(
		applyLinkColor('<a href="https://aiku.test">Shop</a>', "#ff0000"),
		'<a style="text-decoration:underline;color:#ff0000" href="https://aiku.test">Shop</a>',
	)
	assert.equal(
		applyLinkColor('<a href="https://aiku.test">Shop</a>', "#ff0000", false),
		'<a style="text-decoration:none;color:#ff0000" href="https://aiku.test">Shop</a>',
	)
})

test("turning the underline off overrides the underline a link carries in its own style", () => {
	const html = '<a href="https://aiku.test" style="color:#123456;text-decoration: underline">Shop</a>'

	assert.equal(applyLinkColor(html, "#ff0000"), html)
	assert.equal(
		applyLinkColor(html, "#ff0000", false),
		'<a href="https://aiku.test" style="color:#123456;text-decoration:none">Shop</a>',
	)
})

test("a paragraph renders its links without underline when the block turns it off", () => {
	const paragraph = createModule(MODULE_TYPES.paragraph)
	paragraph.descriptor.paragraph.html = '<p><a href="https://aiku.test">Shop</a></p>'
	paragraph.descriptor.paragraph.computedStyle.linkUnderline = false

	const html = renderModuleHtml(paragraph, createRenderContext(emailWithBody(), 600))

	assert.match(html, /<a style="text-decoration:none;color:#0068A5" href="https:\/\/aiku.test">/)
})

test("the email wide setting turns the underline off for blocks that do not set it", () => {
	const paragraph = createModule(MODULE_TYPES.paragraph)
	paragraph.descriptor.paragraph.html = '<p><a href="https://aiku.test">Shop</a></p>'

	const html = renderModuleHtml(paragraph, createRenderContext(emailWithBody({ linkUnderline: false }), 600))

	assert.match(html, /text-decoration:none/)
})

test("the sent email shrinks a heading on mobile through a media rule, and the mobile canvas shows that size", () => {
	const heading = createModule(MODULE_TYPES.heading)
	heading.descriptor.heading.style["font-size"] = "38px"
	heading.descriptor.mobileStyle = { "font-size": "24px", "padding-top": "4px" }

	const sentContext = createRenderContext(emailWithBody(), 600)
	const sentHtml = renderModuleHtml(heading, sentContext)
	assert.match(sentHtml, /<h1 style="margin:0;[^"]*font-size:38px/)
	assert.deepEqual(sentContext.mobileRules, [
		".block-1 .pad{padding-top:4px!important}",
		".block-1 h1{font-size:24px!important}",
	])

	const mobileCanvasContext = createRenderContext(emailWithBody(), 375, true, true)
	const mobileCanvasHtml = renderModuleHtml(heading, mobileCanvasContext)
	assert.match(mobileCanvasHtml, /<h1 style="margin:0;[^"]*font-size:24px/)
	assert.match(mobileCanvasHtml, /class="pad" style="[^"]*padding-top:4px/)
	assert.deepEqual(mobileCanvasContext.mobileRules, [])

	const desktopCanvasHtml = renderModuleHtml(heading, createRenderContext(emailWithBody(), 600, true))
	assert.match(desktopCanvasHtml, /<h1 style="margin:0;[^"]*font-size:38px/)
})

test("mobile styles saved as an empty list by older layouts are ignored", () => {
	const heading = createModule(MODULE_TYPES.heading)
	heading.descriptor.mobileStyle = []

	const context = createRenderContext(emailWithBody(), 375, true, true)

	assert.match(renderModuleHtml(heading, context), /font-size:32px/)
})
