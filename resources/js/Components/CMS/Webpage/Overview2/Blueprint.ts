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
