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
                    name: "Phone",
                    type: "arrayPhone"
                },
                {
                    key: ["caption"],
                    name: "caption",
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
                    name: "Phone",
                    type: "text"
                },
                {
                    key: ["caption"],
                    name: "caption",
                    type: "text"
                },
                {
                    key: ["message"],
                    name: "message",
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
                    name: "Payments",
                    type: "payment_templates",
                },
                {
                    key: ["label"],
                    name: "Label",
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
