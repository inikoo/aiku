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
                {
                    key: ["text"],
                    label: "Text",
                    type: "textProperty"
                }
            ]
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
					label :'Visibility',
					type: "VisibleLoggedIn",
				},
				{
					key: ["text"],
					label :'Text',
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
					label :'Visibility',
					type: "VisibleLoggedIn",
				},
				{
					key: ["text"],
					label :'Text',
					type: "editorhtml",
				},
			],
		},
		{
			name: "Login",
            key:['login'],
			icon: {
				icon: "fal fa-sign-in-alt",
				tooltip: "Action",
			},
			replaceForm: [
				{
					key: ["visible"],
					label :'Visibility',
					type: "VisibleLoggedIn",
				},
				{
					key: [],
					label :'Button',
					type: "button",
				},
			],
		},
		{
			name: "Register",
            key:["register"],
			icon: {
				icon: "fal fa-dot-circle",
				tooltip: "Action",
			},
			replaceForm: [
				{
					key: ["visible"],
					label :'Visibility',
					type: "VisibleLoggedIn",
				},
				{
					key: [],
					label :'Button',
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
					label :'Visibility',
					props_data: {
						defaultValue: 'login',
					},
				},
				{
					key: ["link"],
					type: "link",
					label :'Link',
					props_data: {
						defaultValue: {
							type : "external",
							url: "",
							id: null,
							workshop_route : ""
						},
					},
				},
				{
					key: ['container', 'properties'],
					type: "button",
					label :'Button',
				},
				{
					key: ['text'],
					type: "editorhtml",
					label :'Text',
					props_data: {
						defaultValue: '{{ cart_count }}',
					},
				},
			],
		},
		{
			name: "Favourite",
            key: ["favourite"],
			icon: {
				icon: "fal fa-heart",
				tooltip: "Favourite",
			},
			replaceForm: [
				{
					key: ["visible"],
					type: "VisibleLoggedIn",
					label :'Visibility',
					props_data: {
						defaultValue: 'login',
					},
				},
				{
					key: ["link"],
					type: "link",
					label :'Link',
					props_data: {
						defaultValue: {
							"type" : "external",
							"url": "",
							"id": null,
							"workshop_route" : ""
						},
					},
				},
				{
					key: ['container', 'properties'],
					type: "button",
					label :'Button',
				},
				{
					key: ['text'],
					type: "editorhtml",
					label :'text',
					props_data: {
						defaultValue: '{{ favourites_count }}',
					},
				},
			],
		},
		{
			name: "Profile",
            key: ["profile"],
			icon: {
				icon: "fal fa-user",
				tooltip: "Profile",
			},
			replaceForm: [
				{
					key: ["visible"],
					type: "VisibleLoggedIn",
					label :'Visibility',
					props_data: {
						defaultValue: 'login',
					},
				},
				{
					key: ['container', 'properties'],
					type: "button",
					label :'Button',
					props_data: {
						defaultValue: {
							"text": {
								"color": "rgba(255, 255, 255, 1)"
							},
							"padding": {
								"top": {
									"value": 5
								},
								"left": {
									"value": 10
								},
								"unit": "px",
								"right": {
									"value": 10
								},
								"bottom": {
									"value": 5
								}
							}
						},
					},
				},
				{
					key: ['text'],
					type: "editorhtml",
					label :'Text',
					props_data: {
						defaultValue: '{{ name }}',
					},
				},
			],
		},
	],
}
