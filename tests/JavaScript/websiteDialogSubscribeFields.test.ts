import assert from "node:assert/strict"
import test from "node:test"
import {
	getWebsiteDialogBlueprint,
	isSubscribePhoneShown,
} from "../../resources/js/Components/Websites/WebsiteDialog/Templates/Blueprint.ts"

test("the phone field is shown unless the dialog turns it off", () => {
	assert.equal(isSubscribePhoneShown(undefined), true)
	assert.equal(isSubscribePhoneShown({}), true)
	assert.equal(isSubscribePhoneShown({ show_phone: true }), true)
	assert.equal(isSubscribePhoneShown({ show_phone: false }), false)
})

test("the subscribe form lets the phone field be hidden but never the email field", () => {
	const subscribeSection = getWebsiteDialogBlueprint("dialog-subscribe").find((section) => section.accordion_key === "subscribe")
	const fieldKeys = subscribeSection.replaceForm.map((field: { key: string[] }) => field.key[0])

	assert.ok(fieldKeys.includes("show_phone"))
	assert.ok(!fieldKeys.includes("show_email"))
})
