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
            key:['login'],
			icon: {
				icon: "far fa-dot-circle",
				tooltip: "Action",
			},
			replaceForm: [
				{
					key: ["visible"],
					type: "VisibleLoggedIn",
				},
				{
					key: [],
					type: "button",
				},
			],
		},
		{
			name: "Register",
            key:["register"],
			icon: {
				icon: "far fa-dot-circle",
				tooltip: "Action",
			},
			replaceForm: [
				{
					key: ["visible"],
					type: "VisibleLoggedIn",
				},
				{
					key: [],
					type: "button",
				},
			],
		},
		{
			name: "Cart",
            key: ["cart"],
			icon: {
				icon: "fal fa-shopping-cart",
				tooltip: "Cart",
			},
			replaceForm: [
				{
					key: ["visible"],
					type: "VisibleLoggedIn",
					props_data: {
						defaultValue: 'login',
					},
				},
				{
					key: [],
					type: "button",
				},
			],
		},
		// {
		// 	name: "Button 4",
        //     key:["button_4"],
		// 	icon: {
		// 		icon: "far fa-dot-circle",
		// 		tooltip: "Action",
		// 	},
		// 	replaceForm: [
		// 		{
		// 			key: ["visible"],
		// 			type: "VisibleLoggedIn",
		// 		},
		// 		{
		// 			key: [],
		// 			type: "button",
		// 		},
		// 	],
		// },
		// {
		// 	name: "Button 5",
        //     key:['button_5'],
		// 	icon: {
		// 		icon: "far fa-dot-circle",
		// 		tooltip: "Action",
		// 	},
		// 	replaceForm: [
		// 		{
		// 			key: ["visible"],
		// 			type: "VisibleLoggedIn",
		// 		},
		// 		{
		// 			key: ["button_5"],
		// 			type: "button",
		// 		},
		// 	],
		// },
		// {
		// 	name: "Button 6",
        //     key: ["button_6"],
		// 	icon: {
		// 		icon: "far fa-dot-circle",
		// 		tooltip: "Action",
		// 	},
		// 	replaceForm: [
		// 		{
		// 			key: ["visible"],
		// 			type: "VisibleLoggedIn",
		// 		},
		// 		{
		// 			type: 'button',
		// 		},
		// 	],
		// },
	],
}
