import assert from "node:assert/strict"
import test from "node:test"
import { chatEmailRecipient, chatEmailRecipientLabel } from "../../resources/js/Composables/chatEmailRecipient.ts"

test("the reply goes to whoever wrote last, named as they signed", () => {
	const metadata = {
		email_from: "first@example.com",
		email_from_name: "First Writer",
		email_reply_to: "Purchasing@Example.com",
		email_reply_to_name: "Purchasing",
	}

	assert.deepEqual(chatEmailRecipient(metadata), { address: "purchasing@example.com", name: "Purchasing" })
	assert.equal(chatEmailRecipientLabel(metadata), "Purchasing <purchasing@example.com>")
})

test("falls back to the sender of the first email", () => {
	const metadata = { email_from: "first@example.com", email_from_name: "First Writer" }

	assert.equal(chatEmailRecipientLabel(metadata), "First Writer <first@example.com>")
})

test("shows the bare address when the sender gave no name", () => {
	assert.equal(chatEmailRecipientLabel({ email_from: "first@example.com" }), "first@example.com")
	assert.equal(
		chatEmailRecipientLabel({ email_from: "first@example.com", email_from_name: "First", email_reply_to: "last@example.com" }),
		"last@example.com"
	)
})

test("has nothing to show for a conversation that is not an email", () => {
	assert.equal(chatEmailRecipientLabel(null), "")
	assert.equal(chatEmailRecipientLabel({}), "")
})
