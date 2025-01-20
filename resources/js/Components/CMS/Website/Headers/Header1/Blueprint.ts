export default {
	blueprint: [
		{
			name: "Container",
			icon: {
				icon: "fal fa-rectangle-wide",
				tooltip: "Container",
			},
			key: ["container", "properties"],
			replaceForm: [
                {
                    key: ["background"],
                    label: "Background",
                    type: "background"
                },
              /*   {
                    key: ["text"],
                    label: "Text",
                    type: "textProperty"
                } */
            ]		
		},
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
					key: ['properties','dimension'],
					label : "Dimension",
					type: "dimension",
				},
				{
					key: ["image"],
					label : "Upload image",
					type: "upload_image",
				},
				{
					key: ['properties','margin'],
					label : "Margin",
					type: "margin",
				},
				{
					key: ['properties','padding'],
					label : "Padding",
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
		{
			name: "Button 1",
			key: ["button_1"],
			icon: {
				icon: "fal fa-sign-in-alt",
				tooltip: "Action",
			},
			replaceForm: [
				{
					key: ["visible"],
					label : "Visible",
					type: "VisibleLoggedIn",
				},
				{
					key: [],
					label : "Button",
					type: "button",
				},
			],
		},
	],
}
