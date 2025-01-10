export default {
	blueprint: [
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
		{
			name: "Image",
			key: ["image"],
			replaceForm: [
				{
					key: ["visible"],
					type: ["VisibleLoggedIn"],
				},
				{
					type: "upload_image",
					props_data: {},
				},
			],
		},
	],
}
