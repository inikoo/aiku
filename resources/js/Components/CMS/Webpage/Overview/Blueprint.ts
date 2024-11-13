export default {
	blueprint: [
		{
			name: "Properties",
			key: ["container", "properties"],
			replaceForm: [
				{
					key: ["dimension"],
					type: "dimension",
					props_data: {},
				},
				{
					key: ["padding"],
					type: "padding",
					props_data: {},
				},
				{
					key: ["margin"],
					type: "margin",
					props_data: {},
				},
				{
					key: ["border"],
					type: "border",
					props_data: {},
				},
			],
		},
		/* {
			name: "Setting",
			key: ["texts"],
			replaceForm: [
				{
					key: ["texts"],
					type: "overview_form",
					name: "Text",
					props_data: {
						type: "text",
					},
				},
				{
					key: ["images"],
					type: "overview_form",
					name: "Images",
					props_data: {
						type: "images",
					},
				},
			],
		}, */
	],
}
