export default {
	blueprint: [
		{
			name: "Properties",
			key: ["container", "properties"],
			replaceForm: [
				{
					key: ["background"],
					label : "Background",
					type: "background",
				},
				{
					key: ["padding"],
					label : "Padding",
					type: "padding",
				},
				{
					key: ["margin"],
					label : "Margin",
					type: "margin",
				},
				{
					key: ["border"],
					label : 'Border',
					type: "border",
				},
			],
		},
		{
			name: "Column Left",
			key: ["column1"],
            type: "upload_image",
		},
		{
			name: "Column middle top",
			key: ["column2"],
            type: "upload_image",
		},
		{
			name: "Column middle bottom",
			key: ["column3"],
            type: "upload_image",
		},
		{
			name: "Column right",
			key: ["column4"],
            type: "upload_image",
		},
	],
}
