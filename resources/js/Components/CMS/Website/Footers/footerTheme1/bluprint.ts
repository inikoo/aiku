export default {
	blueprint: [
        {
            key: ["container", "properties"],
            name: "Body",
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
                    label: "Caption",
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
                    label: "Caption",
                    type: "text"
                },
                {
                    key: ["message"],
                    label: "Message",
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
