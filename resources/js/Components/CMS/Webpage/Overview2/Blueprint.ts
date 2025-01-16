export default {
	blueprint: [
		{
			name: "Image",
			key: ["image"],
			type: "upload_image",
		},
		{
			name: "Properties",
			key: ["container", "properties"],
			replaceForm: [
				{
					key: ["dimension"],
					label : "Dimension", 
					type: "dimension",
					props_data: {},
				},
				{
					key: ["padding"],
					label : "Padding", 
					type: "padding",
					props_data: {},
				},
				{
					key: ["margin"],
					label : "Margin", 
					type: "margin",
					props_data: {},
				},
				{
					key: ["border"],
					label : "Border", 
					type: "border",
					props_data: {},
				},
			],
		},
	],
}
