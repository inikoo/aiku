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
			name: "Register",
            key:["register"],
			icon: {
				icon: "fal fa-dot-circle",
				tooltip: "Register",
			},
			replaceForm: [
				{
					key: ["visible"],
					type: "VisibleLoggedIn",
					props_data: {
						defaultValue: "logout",
					}
				},
				{
					key: [],
					type: "button",
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
				icon: "fal fa-sign-in-alt",
				tooltip: "Action",
			},
			replaceForm: [
				{
					key: ["visible"],
					type: "VisibleLoggedIn",
					props_data: {
						defaultValue: 'logout',
					},
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
					key: ["link"],
					type: "link",
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
				},
				{
					key: ['text'],
					type: "editorhtml",
					props_data: {
						defaultValue: '{{ cart_count }} items',
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
					props_data: {
						defaultValue: 'login',
					},
				},
				{
					key: ["link"],
					type: "link",
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
				},
				{
					key: ['text'],
					type: "editorhtml",
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
					props_data: {
						defaultValue: 'login',
					},
				},
				{
					key: ["link"],
					type: "link",
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
				},
				{
					key: ['text'],
					type: "editorhtml",
					props_data: {
						defaultValue: 'Welcome, <strong>{{ name }}</strong>!',
					},
				},
			],
		},
	],
}
