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
		/* {
			label : 'text',
			key: ["text"],
			type: "text",
		},
		{
			name: "test2",
			key: ["container", "test2"],
			replaceForm: [
				{
					key: ["test3"],
					replaceForm: [
						{
							name : 'test2',
							key: ["margin"],
							type: "padding",
						},
					]
				},
			],
		}, */
	],
}