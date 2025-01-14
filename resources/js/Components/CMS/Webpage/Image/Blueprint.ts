export default {
    blueprint: [
      {
        name: "Properties",
        key: ["container", "properties"],
        replaceForm: [
          {
            key: ["background"],
            label:"Background",
            type: "background",
          },
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
      {
        name: "Images",
        key: ["value"],
        replaceForm: [
          {
            key: ["layout_type"],
            label:"Layout",
            type: "layout_type",
          },
          /* {
            label: "Images",
            key: ["images"],
            type: "images-property",
          } */
        ],
      },
     
    ]
  }