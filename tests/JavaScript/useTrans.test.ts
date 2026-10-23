import assert from "node:assert/strict"
import test from "node:test"
import { nextTick } from "vue"
import { I18n } from "laravel-vue-i18n"
import { ctrans, ctransChoice } from "../../resources/js/Composables/useTrans.ts"

const loadMessages = (lang: string, messages: Record<string, string>) =>
	I18n.getSharedInstance({ lang, resolve: () => messages }, true)

test("translates, falls back to the text and applies replacements", () => {
	loadMessages("es", { "Hello :name": "Hola :name", "item|items": "artículo|artículos" })

	assert.equal(ctrans("Hello :name", { name: "Ana" }), "Hola Ana")
	assert.equal(ctrans("Missing :name", { name: null }), "Missing ")
	assert.equal(ctrans(""), "")
	assert.equal(ctransChoice("item|items", 2), "artículos")
	assert.equal(ctransChoice(":count box|:count boxes", 3), "3 boxes")
})

test("leaves no effect behind that re-runs when another request loads its language", async () => {
	loadMessages("pt", { "Download File": "Descargar archivo", "item|items": "artículo|artículos" })

	for (let i = 0; i < 100; i++) {
		ctrans("Download File")
		ctransChoice("item|items", i)
	}

	const i18n = I18n.getSharedInstance()
	const findTranslation = i18n.findTranslation.bind(i18n)
	let lookups = 0
	i18n.findTranslation = (key) => {
		lookups++
		return findTranslation(key)
	}

	loadMessages("cs", { "Download File": "Stáhnout soubor", "item|items": "položka|položky|položek" })
	await nextTick()

	assert.equal(lookups, 0)
	assert.equal(ctrans("Download File"), "Stáhnout soubor")
})
