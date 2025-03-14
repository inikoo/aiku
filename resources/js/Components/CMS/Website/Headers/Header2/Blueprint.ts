import { trans } from "laravel-vue-i18n"

export default {
	blueprint: [
		/* 	{
			name: "Texts",
			key: ["text"],
			type: "textHeader",
			props_data: {
				type: "text",
			},
		}, */
		{
			name: "Logo",
			key: ["logo"],
			icon: {
				icon: "fal fa-image",
				tooltip: "Logo",
			},
			replaceForm: [
				{
					key: ["image", "source"],
					label: "Upload image",
					type: "upload_image",
				},
				{
					key: ["image", "alt"],
					label: "Alternate Text",
					type: "text",
				},
				{
					key: ["properties", "dimension"],
					label: "Dimension",
					type: "dimension",
				},
				{
					key: ["properties", "margin"],
					label: "Margin",
					type: "margin",
				},
				{
					key: ["properties", "padding"],
					label: "Padding",
					type: "padding",
				},
				{
					key: ["image", "attributes", "fetchpriority"],
					label: trans("Fetch Priority"),
					information: trans(
						"Priority of the image to loaded. Higher priority images are loaded first (good for LCP)."
					),
					type: "select",
					props_data: {
						placeholder: trans("Priority"),
						options: [
							{
								label: trans("High"),
								value: "high",
							},
							{
								label: trans("Low"),
								value: "low",
							},
						],
					},
				},
			],
		},
	],
}
