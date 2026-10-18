// Opens the browser's print dialog on a PDF without leaving the page. The browser prints with
// the paper and scale chosen on this computer, so it needs no PrintNode.
// Same approach as print-js: the PDF is loaded into an invisible iframe (not display:none,
// Chrome does not render those) and printed from there. A browser that refuses gets the PDF
// in a new tab instead, to print with Ctrl+P.
let printFrame: HTMLIFrameElement | null = null

export const printPdfInBrowser = (pdf: Blob) => {
    const url = URL.createObjectURL(pdf)

    if (printFrame) {
        URL.revokeObjectURL(printFrame.src)
        printFrame.remove()
    }

    printFrame = document.createElement("iframe")
    printFrame.setAttribute("style", "visibility: hidden; position: absolute; width: 0; height: 0; border: 0")
    printFrame.onload = () => {
        setTimeout(() => {
            try {
                printFrame?.contentWindow?.focus()
                printFrame?.contentWindow?.print()
            } catch {
                window.open(url, "_blank")
            }
        }, 100)
    }
    printFrame.src = url
    document.body.appendChild(printFrame)
}
