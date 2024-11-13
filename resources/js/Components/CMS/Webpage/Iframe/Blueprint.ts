export default {
	blueprint: [
		{
			name: "Link",
			key: ["link"],
			type: "text",
			props_data: {
				placeholder: "https://",
			},
		},
		{
			name: "Properties",
			key: ["container", "properties"],
			replaceForm: [
				{
					key: ["dimension"],
					type: "dimension",
				},
				{
					key: ["padding"],
					type: "padding",
				},
				{
					key: [ "margin"],
					type: "margin",
				},
				{
					key: ["border"],
					type: "border",
				},
			],
		},
	],
}
