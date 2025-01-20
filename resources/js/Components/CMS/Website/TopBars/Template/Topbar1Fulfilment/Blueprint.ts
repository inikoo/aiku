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
			name: "Login",
            key:['login'],
			icon: {
				icon: "fal fa-sign-in-alt",
				tooltip: "Action",
			},
			replaceForm: [
				{
					key: ["container",'properties','background'],
					label :'Background',
					type: "background",
				},
				{
					key: ["container",'properties','text'],
					label :'Text',
					type: "textProperty",
				},
				{
					key: ['text'],
					label :'Button Text',
					type: "text",
				},
				{
					key: ["container",'properties','border'],
					label :'Border',
					type: "border",
				},
				{
					key: ["container",'properties','margin'],
					label :'Margin',
					type: "margin",
				},
				{
					key: ["container",'properties','padding'],
					label :'Padding',
					type: "padding",
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
					key: ['container', 'properties'],
					type: "button",
					label :'Button',
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
		{
			name: "Logout",
            key:['logout'],
			icon: {
				icon: "fal fa-sign-out-alt",
				tooltip: "Action",
			},
			replaceForm: [
				{
					key: ["container",'properties','text'],
					label :'Text',
					type: "textProperty",
				},
				{
					key: ['text'],
					label :'Button Text',
					type: "text",
				},
				{
					key: ["container",'properties','border'],
					label :'Border',
					type: "border",
				},
				{
					key: ["container",'properties','margin'],
					label :'Margin',
					type: "margin",
				},
				{
					key: ["container",'properties','padding'],
					label :'Padding',
					type: "padding",
				},
			],
		},
	],
}
