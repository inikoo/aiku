import assert from "node:assert/strict"
import { readFileSync, readdirSync } from "node:fs"
import { join } from "node:path"
import test from "node:test"

const root = join(import.meta.dirname, "..", "..")
const langDirectory = join(root, "lang")
const dimensionKey = "This will overwrite :count products dimensions, are you sure want to continue ?"

const filesUnder = (directory: string): string[] =>
	readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
		const path = join(directory, entry.name)
		if (entry.isDirectory()) {
			return filesUnder(path)
		}
		return /\.(vue|ts|js)$/.test(entry.name) ? [path] : []
	})

const languageFiles = readdirSync(langDirectory).filter((name) => name.endsWith(".json"))

test("no live ctrans call builds its key from an interpolated template literal", () => {
	const offenders = filesUnder(join(root, "resources", "js")).flatMap((path) =>
		readFileSync(path, "utf8")
			.split("\n")
			.filter((line) => !line.trim().startsWith("//") && /ctrans\(`[^`]*\$\{/.test(line))
			.map((line) => `${path}: ${line.trim()}`)
	)

	assert.deepEqual(offenders, [])
})

test("the update-dimensions confirmation is looked up by a :count key in every language that translates it", () => {
	const translated = languageFiles.filter((name) => {
		const strings = JSON.parse(readFileSync(join(langDirectory, name), "utf8"))
		assert.ok(
			!Object.keys(strings).some((key) => key.includes("${totalProductsForDimensionUpdate}")),
			`${name} still carries the interpolated key`
		)
		return dimensionKey in strings
	})

	assert.ok(translated.includes("pl.json"))
	assert.ok(translated.includes("en.json"))

	for (const name of translated) {
		const value = JSON.parse(readFileSync(join(langDirectory, name), "utf8"))[dimensionKey]
		assert.equal(value.match(/:count\b/g)?.length, 1, `${name} must keep :count exactly once`)
	}
})

test("the update-dimensions confirmation survives t:up through the glossary of every language that translates it", () => {
	const glossary = JSON.parse(readFileSync(join(root, "resources", "translation-glossary.json"), "utf8"))

	for (const name of languageFiles.filter((name) => name !== "en.json")) {
		const locale = name.replace(/\.json$/, "")
		const strings = JSON.parse(readFileSync(join(langDirectory, name), "utf8"))
		if (dimensionKey in strings) {
			assert.equal(glossary[locale]?.[dimensionKey], strings[dimensionKey], `${locale} glossary`)
		}
	}
})

test("the Retina basket recommendations translate through ctrans, not a trans alias", () => {
	const source = readFileSync(
		join(root, "resources", "js", "Components", "Retina", "BasketRecommendationsInternal.vue"),
		"utf8"
	)

	assert.match(source, /import \{ ctrans \} from '@\/Composables\/useTrans'/)
	assert.doesNotMatch(source, /ctrans as trans/)
	assert.doesNotMatch(source, /(?<![\w.])trans\(/)
})

const fullyTranslatedLocales = ["sk", "es"]

test("every fully translated language keeps the placeholders of its key", () => {
	const placeholders = (text: string) => [...new Set(text.match(/(?<![\w/]):[A-Za-z_][A-Za-z0-9_]*/g) ?? [])].sort()

	for (const locale of fullyTranslatedLocales) {
		const strings: Record<string, string> = JSON.parse(readFileSync(join(langDirectory, `${locale}.json`), "utf8"))
		const offenders = Object.entries(strings)
			.filter(([key, value]) => placeholders(key).join() !== placeholders(value).join())
			.map(([key]) => key)

		assert.deepEqual(offenders, [], locale)
	}
})

test("every Spanish question and exclamation opens with its inverted mark", () => {
	const spanish: Record<string, string> = JSON.parse(readFileSync(join(langDirectory, "es.json"), "utf8"))
	const offenders = Object.entries(spanish)
		.filter(([key, value]) => {
			const text = value.replace(/https?:\/\/\S+/g, "").replace(/<[^>]*>/g, "")
			return (text.includes("?") && !text.includes("¿")) || (key.includes("!") && text.includes("!") && !text.includes("¡"))
		})
		.map(([key]) => key)

	assert.deepEqual(offenders, [])
})

test("a count is never glued to a bare translated word", () => {
	const countWords = ["touches", "chats", "labels", "units", "hours", "blocks", "lines selected", "stocks selected", "requests", "views", "others", "days out of stock", "new registrations"]
	const glued = new RegExp(`\\}\\}?\\s*(\\{\\{|\\$\\{)\\s*c?trans\\((["'])(${countWords.join("|")})\\2\\)`)
	const offenders = filesUnder(join(root, "resources", "js")).flatMap((path) =>
		readFileSync(path, "utf8")
			.split("\n")
			.filter((line) => glued.test(line) || /:packs\)['"], \{ packs: [^}]*\+ ['"]s['"]/.test(line))
			.map((line) => `${path}: ${line.trim()}`)
	)

	assert.deepEqual(offenders, [])
})

test("every plural key gives Slovak its three forms and falls back to English", () => {
	const english: Record<string, string> = JSON.parse(readFileSync(join(langDirectory, "en.json"), "utf8"))
	const slovak: Record<string, string> = JSON.parse(readFileSync(join(langDirectory, "sk.json"), "utf8"))
	const pluralKeys = Object.keys(english).filter((key) => /^(hour|[^|]*:(count|number|progress) [^|]*)\|[^| ][^|]*$/.test(key) && !key.includes(" | "))

	assert.ok(pluralKeys.includes(":count unit|:count units"))
	assert.ok(pluralKeys.includes("hour|hours"))
	assert.equal(slovak[":number passed"].split("|").length, 3)
	assert.deepEqual(pluralKeys.filter((key) => english[key] !== key || slovak[key].split("|").length !== 3), [])

	const placeholders = (text: string) => [...new Set(text.match(/:[a-z_]+/g) ?? [])].sort().join()
	const formsByLocale: Record<string, number> = { cs: 3, hr: 3, pl: 3, ro: 3, sk: 3, uk: 3 }

	for (const name of languageFiles.filter((name) => name !== "en.json" && !name.startsWith("php_") && name !== "texts_to_translate.json")) {
		const locale = name.replace(/\.json$/, "")
		const strings: Record<string, string> = JSON.parse(readFileSync(join(langDirectory, name), "utf8"))
		assert.ok(strings[":count unit|:count units"], name)
		const offenders = pluralKeys.filter((key) => key in strings).filter((key) => {
			const forms = strings[key].split("|")
			return forms[0] === "" || forms.length > (formsByLocale[locale] ?? 2) || forms.some((form) => placeholders(form) !== placeholders(key))
		})
		assert.deepEqual(offenders, [], name)
	}
})

test("a word with two meanings is looked up by a key of its own at each call site", () => {
	const separated: [string, RegExp, string][] = [
		["app/Http/Resources/SupplyChain/SuppliersResource.php", /__\('Free'\)/, "No agent"],
		["app/Actions/SupplyChain/Supplier/UI/IndexSuppliers.php", /__\('Free'\)/, "No agent"],
		["app/Actions/SupplyChain/SupplierProduct/UI/IndexSupplierProducts.php", /__\('Free'\)/, "No agent"],
		["resources/js/Components/Tables/Grp/SupplyChain/TableAssignableSuppliers.vue", /ctrans\("Free"\)/, "No agent"],
		["resources/js/Components/DevOps/ServerUsageCard.vue", /ctrans\("Free"\)/, "Free space"],
		["resources/js/Components/Chat/Agent/MessageAreaAgent.vue", /ctrans\("Off"\)/, "Switched off"],
		["app/Actions/Inventory/OrgStockFamily/UI/IndexOrgStockFamilies.php", /__\('Off'\)/, "Switched off"],
		["resources/js/Pages/Grp/Goods/ProductCommandControl.vue", /ctrans\("OFF"\)/, "OFF SALE"],
		["resources/js/Components/Tickets/TicketControls.vue", /ctrans\("Pass"\)/, "Pass QA"],
		["resources/js/Components/DevOps/CiRunCard.vue", /ctrans\("passed"\)/, ":number passed"],
		["app/Actions/UI/HumanResources/ShowHumanResourcesDashboard.php", /__\('Present'\)/, "Staff present"],
		["app/Actions/HumanResources/EmployeeContract/UI/IndexEmployeeContracts.php", /__\('Present'\)/, "Present day"],
		["app/Actions/Production/PartnerShippingList/UI/IndexPrePickList.php", /__\('Lines?'\)/, "lines"],
		["resources/js/Components/SalesAnalysis/SalesAnalysisReport.vue", /ctrans\("Lines"\)/, "Draw lines"],
		["app/Actions/Production/Restock/UI/ShowToRestock.php", /__\('Watch'\)/, "On watch"],
		["resources/js/Components/DevOps/TelemetryPanel.vue", /ctrans\("Jobs"\)/, "Queue jobs"],
		["resources/js/Components/Tables/Grp/Org/CRM/TableCustomerReorderProducts.vue", /ctrans\("Due"\)/, "Due now"],
		["resources/js/Pages/Grp/Org/HumanResources/LeaveAdmin.vue", /ctrans\('Record'\)/, "Record leave"],
		["resources/js/Components/Seo/SeoAiVisibilityOverview.vue", /ctrans\("Named"\)/, "Brand named"],
		["resources/js/Components/Seo/SeoAiPrompts.vue", /ctrans\("Named"\)/, "Brand named"],
		["resources/js/Components/Offers/ModalCreateStepDiscountProduct.vue", /ctrans\('Add step'\)/, "Add discount step"],
		["app/Actions/Web/WebsiteNotFoundPath/UI/IndexWebsiteNotFoundPaths.php", /__\('Hits'\)/, "404 visits"],
		["resources/js/Components/Seo/SeoRankings.vue", /ctrans\("Shopping"\)/, "Shopping results"],
		["resources/js/Components/Utils/Label/DiscountTemplate/CategoryQuantityOrderedOrderInterval/FamilyOfferLabelGRV2.vue", /ctrans\('(Gold|Reward)'\)/, "Gold Reward"],
		["app/Actions/CRM/TrafficSource/GetShopAttributionDataQuality.php", /__\('touches'\)/, ":count touch|:count touches"],
		["app/Actions/Chat/ChatSession/GetChatShopFacts.php", /__\('units'\)/, "for :count unit|for :count units"],
		["app/Actions/CRM/TrafficSourceCampaign/UI/ShowGoogleAdsCampaign.php", /__\('Review'\)/, "Review campaign"],
		["app/Actions/CRM/TrafficSourceCampaign/UI/ShowGoogleAdsCampaignReview.php", /__\('Review'\)/, "Review :name"],
		["app/Actions/Retina/Dropshipping/Orders/ShowRetinaDropshippingOrder.php", /__\('Review'\)/, "Review order"],
		["app/Actions/Retina/Ecom/Orders/ShowRetinaEcomOrder.php", /__\('Review'\)/, "Review order"],
		["resources/js/Components/Procurement/StockDeliveryInvoiceCosting.vue", /ctrans\('Review'\)/, "Review invoice"],
		["resources/js/Components/Chat/WhatsappCallBar.vue", /ctrans\("Answer"\)/, "Answer call"],
		["resources/js/Layouts/Grp/WhatsappCallAlert.vue", /ctrans\("Answer"\)/, "Answer call"],
		["app/Enums/Helpers/Ticket/TicketLinkTypeEnum.php", /__\('Blocks'\)/, "Is blocking"],
		["resources/js/Pages/Grp/Org/Production/PartnerShippingList.vue", /ctrans\("free"\)/, "not promised"],
		["resources/js/Components/Navigation/Breadcrumbs.vue", /ctrans\('Up'\)/, "Go up a level"],
		["resources/js/Components/Warehouse/Inventory/OrgStockDiscontinuePreviewModal.vue", /ctrans\("Open orders"\)/, "Open customer orders"],
	]
	const english = JSON.parse(readFileSync(join(langDirectory, "en.json"), "utf8"))
	const slovak = JSON.parse(readFileSync(join(langDirectory, "sk.json"), "utf8"))

	for (const [file, sharedKey, ownKey] of separated) {
		const source = readFileSync(join(root, file), "utf8")
		assert.doesNotMatch(source, sharedKey, file)
		assert.ok(source.includes(`'${ownKey}'`) || source.includes(`"${ownKey}"`), `${file} must use ${ownKey}`)
		assert.ok(ownKey in english && ownKey in slovak, `${ownKey} must be in en.json and sk.json`)
	}

	assert.equal(slovak["Free"], "Zadarmo")
	assert.equal(slovak["Off"], "Zľava")
	assert.equal(slovak["Gold Reward"], "Gold Reward")
	assert.notEqual(slovak["Shopping"], slovak["Shopping results"])
})
