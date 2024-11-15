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
				icon: "fal fa-sign-in-alt",
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
											"value": 20
										},
										"unit": "px",
										right: {
											"value": 20
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
				},
				{
					key: [],
					type: "button",
				},
			],
		},
		{
			name: "Button 4",
            key:["button_4"],
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
			name: "Button 5",
            key:['button_5'],
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
					key: ["button_5"],
					type: "button",
				},
			],
		},
		{
			name: "Button 6",
            key: ["button_6"],
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
					type: 'button',
				},
			],
		},
	],
}
