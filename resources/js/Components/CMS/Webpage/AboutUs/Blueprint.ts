export default {
	blueprint: [
		{
			name: "Texts",
			key: ["texts","values"],
			type: "overview-property",
			props_data: {
				type : 'text'
			},
		},
		{
			name: "Images",
			key: ["images"],
			type: "overview-property",
			props_data: {
				type : 'image'
			},
		},
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
	],
}
