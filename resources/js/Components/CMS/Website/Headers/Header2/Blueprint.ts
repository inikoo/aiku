export default {
	blueprint: [
	/* 	{
			name: "Texts",
			key: ["text"],
			type: "textHeader",
			props_data: {
				type: "text",
			},
		}, */
		{
			name: "Logo",
			key: ["logo"],
			icon: {
				icon: "fal fa-image",
				tooltip: "Logo",
			},
			/*     type: "upload_image", */
			replaceForm: [
				{
					key: ["image"],
					label: "Upload image",
					type: "upload_image",
				},
				{
					key: ["properties", "dimension"],
					label: "Dimension",
					type: "dimension",
				},
				{
					key: ["properties", "margin"],
					label: "Margin",
					type: "margin",
				},
				{
					key: ["properties", "padding"],
					label: "Padding",
					type: "padding",
				},
			],
			/* replaceForm: [
				{
					key: ["visible"],
					type: ["VisibleLoggedIn"],
				},
				{
					key: "logo",
					type: "upload_image",
				},
			], */
		},
	],
}
