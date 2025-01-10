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
			name: "Column 1",
			key: ["column1"],
            type: "upload_image",
		},
		{
			name: "Column 2",
			key: ["column2"],
            type: "upload_image",
		},
		{
			name: "Column 3",
			key: ["column3"],
            type: "upload_image",
		},
		{
			name: "Column 4",
			key: ["column4"],
            type: "upload_image",
		},
	],
}
