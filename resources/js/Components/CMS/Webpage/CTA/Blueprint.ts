export default {
	blueprint: [
		{
			name: "Properties",
			key: ["container", "properties"],
			replaceForm: [
				{
					key: ["background"],
					label :"background",
					type: "background",
					
				},
				{
					key: ["padding"],
					label : "padding",
					type: "padding",
					
				},
				{
					key: ["margin"],
					label : "margin",
					type: "margin",
					
				},
				{
					key: ["border"],
					label : "border",
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
