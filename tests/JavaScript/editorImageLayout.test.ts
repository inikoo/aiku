import assert from "node:assert/strict"
import test from "node:test"
import {
	clampImageWidthPercent,
	imageAlignmentStyle,
	imageWidthPercent,
	imageWidthStyle,
	toImageWidth,
} from "../../resources/js/Components/Forms/Fields/BubleTextEditor/CustomResizeImage/imageLayout.ts"

test("an image without a saved width fills the article", () => {
	assert.equal(imageWidthPercent(null), 100)
	assert.equal(imageWidthPercent(""), 100)
	assert.equal(imageWidthStyle(null), "width: 100%; height: auto;")
})

test("reads the width left by dragging a corner as a whole percentage", () => {
	assert.equal(imageWidthPercent("43.6789%"), 44)
	assert.equal(imageWidthPercent(" 50% "), 50)
})

test("does not guess a percentage for a width in another unit", () => {
	assert.equal(imageWidthPercent("300px"), null)
	assert.equal(imageWidthPercent("auto"), null)
})

test("keeps a width between a tenth and the whole of the article", () => {
	assert.equal(clampImageWidthPercent(3), 10)
	assert.equal(clampImageWidthPercent(140), 100)
	assert.equal(clampImageWidthPercent(Number.NaN), 100)
	assert.equal(toImageWidth(62.4), "62%")
	assert.equal(toImageWidth(-5), "10%")
	assert.equal(imageWidthPercent("250%"), 100)
})

test("saves the chosen width with the image", () => {
	assert.equal(imageWidthStyle(toImageWidth(25)), "width: 25%; height: auto;")
})

test("floats an image to the side so the text wraps around it", () => {
	assert.equal(imageAlignmentStyle("left"), "float: left; margin-right: 1rem;")
	assert.equal(imageAlignmentStyle("right"), "float: right; margin-left: 1rem;")
})

test("centres an image that is not floated, and nothing in its style undoes that", () => {
	const centred = `${imageAlignmentStyle("none")} ${imageWidthStyle("40%")}`

	assert.equal(imageAlignmentStyle("none"), "display: block; margin: 0 auto;")
	assert.equal(imageAlignmentStyle(undefined), "display: block; margin: 0 auto;")
	assert.equal(centred.match(/display:/g)?.length, 1)
	assert.equal(centred.match(/width:/g)?.length, 1)
})
