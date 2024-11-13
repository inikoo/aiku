export default {
	blueprint: [
		{
			name: "Properties",
			key: ["container", "properties"],
			replaceForm: [
				{
					key: ["background"],
					type: "background",
				},
				{
					key: ["padding"],
					type: "padding",
				},
				{
					key: ["margin"],
					type: "margin",
				},
				{
					key: ["border"],
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
