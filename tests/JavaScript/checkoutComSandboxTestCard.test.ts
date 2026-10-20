import assert from "node:assert/strict"
import { mkdirSync, mkdtempSync, readFileSync, writeFileSync } from "node:fs"
import { join } from "node:path"
import test from "node:test"
import { compileScript, parse } from "@vue/compiler-sfc"
import { createSSRApp, h } from "vue"
import { renderToString } from "vue/server-renderer"

const root = join(import.meta.dirname, "..", "..")
const source = readFileSync(join(root, "resources/js/Components/Retina/CheckoutComSandboxTestCard.vue"), "utf8")

const loadComponent = async () => {
	const { descriptor } = parse(source)
	const compiled = compileScript(descriptor, { id: "sandbox-test-card", inlineTemplate: true, templateOptions: { ssr: true } })
		.content
		.replace('import { ctrans } from "@/Composables/useTrans"', "const ctrans = (text) => text")
		.replaceAll(": any", "")
	mkdirSync(join(root, "node_modules", ".cache"), { recursive: true })
	const file = join(mkdtempSync(join(root, "node_modules", ".cache", "sandbox-test-card-")), "component.mjs")
	writeFileSync(file, compiled)

	return (await import(file)).default
}

const render = async (environment?: string) => renderToString(createSSRApp({ render: () => h(component, { environment }) }))

const component = await loadComponent()

test("warns against real cards and shows the Checkout.com test card on the sandbox", async () => {
	const html = await render("sandbox")

	assert.match(html, /do not use a real credit or debit card/)

	assert.match(html, /5385 3083 6013 5181/)
	assert.match(html, /Checkout1!/)
})

test("never shows test card details in production or when the environment is missing", async () => {
	assert.doesNotMatch(await render("production"), /5385/)
	assert.doesNotMatch(await render(undefined), /5385/)
	assert.doesNotMatch(await render("production"), /real credit/)
})
