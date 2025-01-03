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
			name: "Logo",
            key:['logo'],
			type: "upload_image",
			icon: {
				icon: "fal fa-image",
				tooltip: "Logo",
			},
			props_data: {
				defaultValue: null,
			}
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
				tooltip: "Register",
			},
			replaceForm: [
				{
					key: ["visible"],
					type: "VisibleLoggedIn",
					label :'Visibility',
					props_data: {
						defaultValue: "logout",
					}
				},
				{
					key: [],
					type: "button",
					label :'Button',
					props_data: {
						defaultValue: {
							container: {
								properties: {
									text: {
										"color": "rgba(10, 10, 10, 1)"
									},
									border: {
										top: {
											"value": 1
										},
										left: {
											"value": 1
										},
										"unit": "px",
										"color": "rgba(10, 10, 10, 1)",
										right: {
											"value": 1
										},
										bottom: {
											"value": 1
										},
										rounded: {
											"unit": "px",
											topleft: {
												"value": 5
											},
											topright: {
												"value": 5
											},
											bottomleft: {
												"value": 5
											},
											bottomright: {
												"value": 5
											}
										}
									},
									margin: {
										top: {
											"value": 0
										},
										left: {
											"value": 0
										},
										"unit": "px",
										right: {
											"value": 0
										},
										bottom: {
											"value": 0
										}
									},
									padding: {
										top: {
											"value": 5
										},
										left: {
											"value": 10
										},
										"unit": "px",
										right: {
											"value": 10
										},
										bottom: {
											"value": 5
										}
									},
									background: {
										"type": "color",
										"color": "rgba(10, 10, 10, 0)",
										image: {
											"original": null
										}
									}
								}
							}
						},
					}
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
					key: ['container', 'properties'],
					type: "button",
					label: "Button",
				},
				{
					key: ['text'],
					type: "editorhtml",
					label :'Text',
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
					label :'Visibility',
					props_data: {
						defaultValue: 'login',
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
				},
				{
					key: ['text'],
					type: "editorhtml",
					label :'Text',
					props_data: {
						defaultValue: 'Welcome, <strong>{{ name }}</strong>!',
					},
				},
			],
		},
	],
}
