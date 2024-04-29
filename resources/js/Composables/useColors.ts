export const useBasicColor = (colorName: string | undefined) => {
    if(!colorName) return ''

    switch (colorName) {
        case "blue":
            return "#009eff"
        
        case "yellow":
            return "#f7d000"
        
        case "green":
            return "#00ce00"
        
        case "red":
            return "#fa6565"
        
        case "orange":
            return "#fcb661"
        
        case "pink":
            return "#e6007e"
        
        case "purple":
            return "#5000e6"
        
        case "indigo":
            return "#1624ff"
    
        default:
            break;
    }
}