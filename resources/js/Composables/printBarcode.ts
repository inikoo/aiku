import JsBarcode from 'jsbarcode';

// Generate barcode function
export const generateBarcode = (barcodeId: string, value: string) => {
    const barcodeElement = document.getElementById(barcodeId);
    if (barcodeElement) {
        JsBarcode(barcodeElement, value, {
            lineColor: '#000',
            width: 2,
            height: 50,
            displayValue: true,
        });
    } else {
        console.error(`Element with ID "${barcodeId}" not found.`);
    }
};

// Print barcode function
export const printBarcode = (barcodeId: string, value: string) => {
    // Ensure the barcode is generated before printing
    generateBarcode(barcodeId, value);

    const svgElement = document.getElementById(barcodeId);
    if (svgElement) {
        // Open a new window to print the barcode
        const printWindow = window.open('/print', '_blank', 'width=600,height=400');
        if (printWindow) {
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Print Barcode</title>
                        <style>
                            body {
                                margin: 0;
                                display: flex;
                                justify-content: center;
                                align-items: center;
                                height: 100vh;
                                background-color: white;
                            }
                            svg {
                                max-width: 100%;
                                max-height: 100%;
                            }
                        </style>
                    </head>
                    <body>${svgElement.outerHTML}</body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        } else {
            console.error("Failed to open print window.");
        }
    } else {
        console.error(`Barcode element with ID "${barcodeId}" not found.`);
    }
};
