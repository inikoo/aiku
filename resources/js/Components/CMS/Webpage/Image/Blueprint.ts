export default {
    blueprint: [
      {
        name: "Images",
        key: ["value"],
        replaceForm: [
          {
            key: ["layout_type"],
            label:"Layout",
            type: "layout_type",
          },
        /*   {
            label: "Images",
            key: ["images"],
            type: "images-property",
          } */
        ],
      },
      {
        name: "Properties",
        key: ["container", "properties"],
        replaceForm: [
          {
            key: ["padding"],
            label:"Padding",
            type: "padding",
          },
          {
            key: ["margin"],
            label:"Margin",
            type: "margin",
          },
          {
            key: ["border"],
            label:"Border",
            type: "border",
          },
        ],
      },
    ]
  }