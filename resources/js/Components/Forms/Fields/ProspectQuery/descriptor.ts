import { trans } from "laravel-vue-i18n";
export default {
    queryLists: [
        { label:  trans("Email") , value: "email" },
        { label: trans("Phone") , value: "phone" },
        { label: trans("Address") , value: "address" },
    ],
    logic: [
        { label:  trans("All") , value: "all" },
        { label:  trans("Any"), value: "any" },
    ],
    contact: [
        { label: trans("Last Contact"), value: true },
        { label: trans("Never"), value: false },
    ],


    schemaForm: [
        {
            label:  trans("Prospect Can Contact By"),
            name: "prospect_can_contact_by",
            information : trans("filter deliveries based on the prospect"),
            value : {
                fields : [],
                logic : 'all'
            },
        },
        {
            label: trans("Tags"),
            name: "tags",
            information :  trans("Filter by SEO tags"),
            value : {
                tag_ids : [],
                logic : 'all'
            },
        },
        {
            label:  trans("Prospect Last Contacted"),

            name: "prospect_last_contacted",
            information : trans("filter recipients based on the last mailshot sent to them"),
            value : {
                state : false,
                argument: {
                    unit: "week",
                    quantity: 1,
                },
            }
        },
    ],

};
