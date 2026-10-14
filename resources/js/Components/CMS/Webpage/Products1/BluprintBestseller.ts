const badgeBlueprint = (name: string, settingsKey: string) => ({
	name,
	key: ["bestseller", settingsKey],
	replaceForm: [
		{
			name: "Icon",
			key: ["icon"],
			replaceForm: [
				{
					key: [ "text","color"],
					label: "Color",
					type: "color",
				},
				{
					key: ["use_icon"],
					label: "Use Icon",
					type: "switch",
					props_data: {
						defaultValue: true,
					}
				},
			],
		},
		{
			name: "Text",
			key: ["text"],
			replaceForm: [
				{
					key: ["caption"],
					label: "Text",
					type: "text",
				},
				{
					key: ["properties", "text"],
					label: "Text style",
					type: "textProperty",
				},
			],
		},
		{
			name: "Layout",
			key: ["container", "properties"],
			replaceForm: [
				{
					key: ["background"],
					useIn: ["desktop", "tablet", "mobile"],
					label: "Background",
					type: "background",
				},
				{
					key: ["border"],
					useIn: ["desktop", "tablet", "mobile"],
					label: "Border",
					type: "border",
				},
			],
		},
	],
})

export default {
	blueprint: [
		badgeBlueprint("New In", "new_in"),
		badgeBlueprint("Back In Stock", "back_in_stock"),
		badgeBlueprint("Bestseller", "bestseller1"),
	],
}
