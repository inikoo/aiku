// Capitalize single word
const capitalizeString = (str) => {
    return str.charAt(0).toUpperCase() + str.slice(1);
}


// Capitalize words on sentence
export const capitalize = (text: string) => {
    if (text) {
        const words = text.split(' ');
        const capitalizedWords = words.map(word => capitalizeString(word));
        return capitalizedWords.join(' ');
    } else {
        return ''
    }

}