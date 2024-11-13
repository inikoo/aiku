export default {
	blueprint: [
		{
			name: "Container",
			icon: {
				icon: "fal fa-rectangle-wide",
				tooltip: "Container",
			},
			key: ["container", "properties"],
			type: "properties",
		},
		{
			name: "Logo",
			key: ["logo"],
            type: "upload_image",
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
		{
			name: "Button 1",
			key: ["button_1"],
			replaceForm: [
				{
					key: ["visible"],
					type: "VisibleLoggedIn",
				},
				{
					key: ["text"],
					type: "editorhtml",
				},
				{
					key: ["properties"],
					type: "properties",
				},
			],
		},
	],
}
