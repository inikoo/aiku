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
			name: "Title",
            key : ["main_title"],
			icon: {
				icon: "fal fa-text",
				tooltip: "Text",
			},
			replaceForm: [
				{
					key: ["visible"],
					type: "VisibleLoggedIn",
				},
				{
					key: ["text"],
					type: "editorhtml",
				},
			],
		},
		{
			name: "Greeting",
            key : ["greeting"],
			icon: {
				icon: "fal fa-text",
				tooltip: "Text",
			},
			replaceForm: [
				{
					key: ["visible"],
					type: "VisibleLoggedIn",
				},
				{
					key: ["text"],
					type: "editorhtml",
				},
			],
		},
		{
			name: "Login",
            key:['button_1'],
			icon: {
				icon: "far fa-dot-circle",
				tooltip: "Action",
			},
            type: "button",
			/* replaceForm: [
				{
					key: ["visible"],
					type: ["VisibleLoggedIn"],
				},
				{
					key: ["button_1"],
					type: ["button"],
				},
			], */
		},
		{
			name: "Register",
            key:["button_2"],
			icon: {
				icon: "far fa-dot-circle",
				tooltip: "Action",
			},
            type: "button",
		/* 	replaceForm: [
				{
					key: ["button_2", "visible"],
					type: ["VisibleLoggedIn"],
				},
				{
					key: ["button_2"],
					type: ["button"],
				},
			], */
		},
		{
			name: "Button 3",
            key: ["button_3"],
			icon: {
				icon: "far fa-dot-circle",
				tooltip: "Action",
			},
            type: "button",
			/* replaceForm: [
				{
					key: ["button_3", "visible"],
					type: ["VisibleLoggedIn"],
				},
				{
					key: ["button_3"],
					type: ["button"],
				},
			], */
		},
		{
			name: "Button 4",
            key:["button_4"],
			icon: {
				icon: "far fa-dot-circle",
				tooltip: "Action",
			},
            type: "button",
			/* replaceForm: [
				{
					key: ["button_4", "visible"],
					type: ["VisibleLoggedIn"],
				},
				{
					key: ["button_4"],
					type: ["button"],
				},
			], */
		},
		{
			name: "Button 5",
            key:['button_5'],
			icon: {
				icon: "far fa-dot-circle",
				tooltip: "Action",
			},
            type: "button",
		/* 	replaceForm: [
				{
					key: ["button_5", "visible"],
					type: ["VisibleLoggedIn"],
				},
				{
					key: ["button_5"],
					type: ["button"],
				},
			], */
		},
		{
			name: "Button 6",
            key: ["button_6"],
			icon: {
				icon: "far fa-dot-circle",
				tooltip: "Action",
			},
            type: "button",
			/* replaceForm: [
				{
					key: ["button_6", "visible"],
					type: ["VisibleLoggedIn"],
				},
				{
					key: ["button_6"],
					type: ["button"],
				},
			],
			props_data: {}, */
		},
	],
}
