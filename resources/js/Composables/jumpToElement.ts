// Jump the view to the an element
export const jumpToElement = (idElement: string) => {
    const targetElement = document.getElementById(idElement);
    if (targetElement) {
      targetElement.scrollIntoView({ behavior: 'smooth' });
    }
}