export default {
	blueprint: [
        {
            key: ["container", "properties"],
            name: "Body",
            type: "properties"
        },
        {
            key: ["phone"],
            name: "Phone",
            replaceForm: [
                {
                    key: ["numbers"],
                    label: "Phone",
                    type: "arrayPhone"
                },
                {
                    key: ["caption"],
                    label: "caption",
                    type: "text"
                }
            ]
        },
        {
            key: ["whatsapp"],
            name: "Whatsapp",
            replaceForm: [
                {
                    key: ["number"],
                    label: "Phone",
                    type: "text"
                },
                {
                    key: ["caption"],
                    label: "caption",
                    type: "text"
                },
                {
                    key: ["message"],
                    label: "message",
                    type: "text"
                }
            ]
        },
        {
            key: ["email"],
            name: "Email",
            type: "text"
        },
        {
            key: ["logo"],
            name: "Logo",
            type: "upload_image"
        },
        {
            key: ["paymentData"],
            name: "Payments",
            replaceForm: [
                {
                    key: ["data"],
                    type: "payment_templates",
                },
                {
                    key: ["label"],
                    label: "Label",
                    type: "hidden"
                }
            ]
        },
        {
            key: ["socialMedia"],
            name: "Social Media",
            type: "socialMedia"
        },
    ]
}
