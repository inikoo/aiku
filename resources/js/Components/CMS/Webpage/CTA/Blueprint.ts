export default {
	blueprint: [
		{
			name: "Properties",
			key: ["container", "properties"],
			replaceForm: [
				{
					key: ["background"],
					lable :"background",
					type: "background",
					
				},
				{
					key: ["padding"],
					lable : "padding",
					type: "padding",
					
				},
				{
					key: ["margin"],
					lable : "margin",
					type: "margin",
					
				},
				{
					key: ["border"],
					lable : "border",
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
