import { Link } from "@tiptap/extension-link"
import { Plugin } from "prosemirror-state"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

const CustomLink = Link.extend({
	addAttributes() {
		return {
			type: {
				default: null,
			},
			workshop: {
				default: null,
			},
			id: {
				default: null,
			},
			content: {
				default: null,
			},
			class: {
				default: "customLink",
			},
			href: {
				default: null,
				parseHTML(element) {
					return element.getAttribute("href")
				},
			},
		}
	},
	addCommands() {
		return {
			setCustomLink:
				(attrs) =>
				({ commands }) => {
					console.log("sdsad", attrs)
					if (!attrs.href) {
						console.warn("The href attribute is required but was not provided.")
						return false
					}
					return commands.setMark("link", attrs)
				},
		}
	},
	addProseMirrorPlugins() {
		return [
			new Plugin({
				props: {
					handleClick(view, pos, event) {
						const linkMark = view.state.schema.marks.link
						const { tr } = view.state
						const attrs = tr.doc
							.nodeAt(pos)
							?.marks.find((mark) => mark.type === linkMark)?.attrs

						if (attrs) {

							// Prevent default link click behavior
							event.preventDefault()

							// Check if workshop URL exists
							if (attrs.workshop) {
								// Show a custom popup/modal with options
								showPopup(attrs.workshop, attrs.content, attrs.href)
							} else {
								// No workshop, just open the regular URL
								window.open(attrs.href, "_blank")
							}
							return true
						}
						return false
					},
				},
			}),
		]
	},
})


function showPopup(workshopUrl, content, defaultUrl) {
  // Create a simple modal
  const bgDrop = document.createElement("div");
  bgDrop.style.position = "fixed";
  bgDrop.style.top = "0";
  bgDrop.style.left = "0";
  bgDrop.style.backgroundColor = "rgba(0,0,0,0.4)";
  bgDrop.style.zIndex = "9998";
  bgDrop.style.height = "100vh";
  bgDrop.style.width = "100vw";
  
  const modal = document.createElement("div");
  modal.style.position = "fixed";
  modal.style.top = "47%";
  modal.style.left = "44%";
  modal.style.transform = "translate(-50%, -50%)";
  modal.style.padding = "20px";
  modal.style.backgroundColor = "white";
  modal.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
  modal.style.zIndex = "9999";
  
  // Add message and buttons
  modal.innerHTML = `
    <div style="display: flex; gap: 10px;">
      <div>
        <button id="goToWorkshop" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
          Go to Workshop
        </button>
      </div>
      <div>
        <button id="goToUrl" style="padding: 10px 20px; background-color: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
          Go to URL
        </button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);
  document.body.appendChild(bgDrop);

  // Wait for the modal to render, then select the buttons and attach event listeners
  const goToWorkshopButton = modal.querySelector("#goToWorkshop");
  const goToUrlButton = modal.querySelector("#goToUrl");

  if (goToWorkshopButton && goToUrlButton) {
    goToWorkshopButton.onclick = () => {
      window.open(workshopUrl, "_blank");
      document.body.removeChild(modal); // Close the modal
      document.body.removeChild(bgDrop); // Close the modal
    };

    goToUrlButton.onclick = () => {
      window.open(defaultUrl, "_blank");
      document.body.removeChild(modal); // Close the modal
      document.body.removeChild(bgDrop); // Close the modal
    };
  } else {
    console.warn("Button elements not found in the modal.");
  }

  // Close the modal when the mouse leaves the modal area (not the backdrop)
  modal.addEventListener("mouseleave", () => {
    document.body.removeChild(modal);
    document.body.removeChild(bgDrop); // Close the modal
  });

  // Prevent the backdrop from closing when mouse leaves, by listening to mouseleave on bgDrop
  bgDrop.addEventListener("mouseleave", (event) => {
    // Only close modal if the mouse leaves the modal itself (not inside bgDrop)
    if (!modal.contains(event.relatedTarget)) {
      document.body.removeChild(modal);
      document.body.removeChild(bgDrop); // Close the modal
    }
  });
}



export default CustomLink
