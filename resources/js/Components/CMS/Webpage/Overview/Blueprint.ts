export default {
	blueprint: [
		{
			name: "Properties",
			key: ["container", "properties"],
			replaceForm: [
				{
					key: ["dimension"],
					type: "dimension",
					label : "Dimension",
					props_data: {},
				},
				{
					key: ["padding"],
					type: "padding",
					label : "Padding",
					props_data: {},
				},
				{
					key: ["margin"],
					type: "margin",
					label : "Margin",
					props_data: {},
				},
				{
					key: ["border"],
					type: "border",
					label : "Border",
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
