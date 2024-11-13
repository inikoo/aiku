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
			name: "Button",
			key: ["button"],
			type: "button",
			
		},
		{
			name: "Image",
			key: "image",
            type: "upload_image",
		/* 	replaceForm: [
				{
					key: ["image", "visible"],
					type: ["VisibleLoggedIn"],
				},
				{
					key: ["image"],
                     type: "upload_image",
				},
			], */
		},
	],
}
