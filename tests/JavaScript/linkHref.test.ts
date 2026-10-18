import assert from "node:assert/strict"
import test from "node:test"
import { buildLinkHref, parseLinkHref } from "../../resources/js/Components/Forms/Fields/BubleTextEditor/linkHref.ts"

test("a web page link keeps its url", () => {
	const fields = parseLinkHref("https://www.awartisan.eu/aromatherapy?utm=1")

	assert.equal(fields.type, "web")
	assert.equal(fields.url, "https://www.awartisan.eu/aromatherapy?utm=1")
	assert.equal(buildLinkHref(fields), "https://www.awartisan.eu/aromatherapy?utm=1")
})

test("an email link is split into address, subject and message and built back", () => {
	const fields = parseLinkHref("mailto:sales@aiku.test?subject=Order%20question&body=Hello%20there")

	assert.deepEqual(
		{ type: fields.type, email: fields.email, subject: fields.subject, body: fields.body },
		{ type: "email", email: "sales@aiku.test", subject: "Order question", body: "Hello there" },
	)
	assert.equal(buildLinkHref(fields), "mailto:sales@aiku.test?subject=Order%20question&body=Hello%20there")
	assert.equal(buildLinkHref({ ...fields, subject: "", body: "" }), "mailto:sales@aiku.test")
})

test("call and sms links use the phone number without spaces", () => {
	assert.equal(buildLinkHref({ ...parseLinkHref(""), type: "phone", phone: "+44 20 1234 5678" }), "tel:+442012345678")
	assert.equal(buildLinkHref({ ...parseLinkHref(""), type: "sms", phone: "+44 7700 900123", body: "Hi & bye" }), "sms:+447700900123?body=Hi%20%26%20bye")

	const sms = parseLinkHref("sms:+447700900123?body=Hi%20%26%20bye")
	assert.equal(sms.type, "sms")
	assert.equal(sms.phone, "+447700900123")
	assert.equal(sms.body, "Hi & bye")
	assert.equal(parseLinkHref("tel:+442012345678").phone, "+442012345678")
})

test("a link type without its address builds no link, so it cannot be saved", () => {
	assert.equal(buildLinkHref(parseLinkHref("")), "")
	assert.equal(buildLinkHref({ ...parseLinkHref(""), type: "email" }), "")
	assert.equal(buildLinkHref({ ...parseLinkHref(""), type: "phone", phone: "  " }), "")
})
