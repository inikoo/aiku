
export const breakpointType = (screenWidth: number) => {
    if(screenWidth < 639) {
        return 'xs'
    } else if(639 < screenWidth && screenWidth < 768){
        return 'sm';
    } else if (767 < screenWidth && screenWidth < 1024) {
        return 'md';
    } else if(1023 < screenWidth && screenWidth < 1280) {
        return 'lg';
    } else if(1279 < screenWidth && screenWidth < 1536) {
        return 'xl';
    } else if(1535 < screenWidth) {
        return '2xl';
    }
}
