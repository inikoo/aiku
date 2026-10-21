import assert from "node:assert/strict"
import test from "node:test"
import { isIosChromeInjectedRecursion, sentryIgnoreErrors } from "../../resources/js/Composables/sentryNoise.ts"

const ignored = (message: string) => sentryIgnoreErrors.some((pattern) => (typeof pattern === "string" ? message.includes(pattern) : pattern.test(message)))

test("third party and deploy noise is dropped", () => {
	for (const message of [
		"ReferenceError: fbq is not defined",
		"ReferenceError: Can't find variable: pintrk",
		"Error: Error invoking postMessage: Java object is gone",
		"TypeError: Failed to fetch dynamically imported module: https://www.ancientwisdom.biz/iris/assets/ProductSoundButton-DP8H_2We.js",
		"Network Error",
		"HTTP Client Error with status code: 503",
		"Request failed with status code 503",
	]) {
		assert.ok(ignored(message), message)
	}
})

test("our own errors still reach sentry", () => {
	for (const message of [
		"TypeError: Cannot read properties of null (reading 'text')",
		"ReferenceError: product is not defined",
		"Request failed with status code 500",
		"ReferenceError: richSnippetReviewsWidgets is not defined",
	]) {
		assert.ok(!ignored(message), message)
	}
})

const recursion = { exception: { values: [{ type: "RangeError", value: "Maximum call stack size exceeded." }] } }

test("the recursion iOS Chrome and the Google app inject is dropped, ours still reports", () => {
	assert.ok(isIosChromeInjectedRecursion(recursion, "Mozilla/5.0 (iPhone; CPU iPhone OS 26_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/141.0 Mobile/15E148 Safari/604.1"))
	assert.ok(isIosChromeInjectedRecursion(recursion, "Mozilla/5.0 (iPhone; CPU iPhone OS 26_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) GSA/443.5.997327796 Mobile/15E148 Safari/604.1"))
	assert.ok(!isIosChromeInjectedRecursion(recursion, "Mozilla/5.0 (iPhone; CPU iPhone OS 26_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1"))
	assert.ok(!isIosChromeInjectedRecursion({ exception: { values: [{ type: "TypeError", value: "x is undefined" }] } }, "CriOS/141.0"))
})
