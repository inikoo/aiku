/**
 *  author: Vika Aqordi
 *  created on: 18-10-2024
 *  github: https://github.com/aqordeon
 *  copyright: 2024
*/


// Check if the user is logged in
export const checkVisible = (visible: string | null, isLoggedIn: boolean) => {
    if (!visible) return false

    if (visible === 'all') {
        return true
    } else if (visible === 'login') {
        return isLoggedIn
    } else {
        return !isLoggedIn
    }
}

// all, logout, login 
export const viewVisible = (mode = true , visibilty = 'all') =>{
    if(visibilty == 'all') return true
    else if(mode && visibilty == "logout") return false
    else if(!mode && visibilty == "login") return false
    else return true
}

export const setIframeView = (view: String) => {
    if (view === 'mobile') {
        return 'w-[375px] h-full mx-auto';
    } else if (view === 'tablet') {
        return 'w-[768px] h-full mx-auto';
    } else {
        return 'w-full h-full';
    }
}

// Send data to parent window
export const iframeToParent = (data: any) => {
    if (window) {
        window.parent.postMessage(data, '*')
    }
}

// Send data to parent window
export const sendMessageToParent = (key: string, value: any) => {
    const serializableValue = JSON.parse(JSON.stringify(value));
    window.parent.postMessage({ key, value: serializableValue }, '*');
}

export const textReplaceVariables = (text: string, piniaVariables: {}) => {
    if (!text) {
        return ''
    }

    return text.replace(/\{\{\s*name\s*\}\}/g, piniaVariables?.name || 'Vika A.')
    .replace(/\{\{\s*username\s*\}\}/g, piniaVariables?.username || 'aqordeon')
    .replace(/\{\{\s*email\s*\}\}/g, piniaVariables?.email || 'example@mail.com')
    .replace(/\{\{\s*favourites_count\s*\}\}/g, piniaVariables?.favourites_count || '0')
    .replace(/\{\{\s*cart_count\s*\}\}/g, piniaVariables?.cart_count || '0')
    .replace(/\{\{\s*cart_amount\s*\}\}/g, piniaVariables?.cart_amount || '0')
}


// Method: to declare css variable in the root
export const irisStyleVariables = (layoutColor: string[]) => {
    if (!layoutColor) {
        return 
    }

    const root = document.documentElement
    root.style.setProperty('--iris-color-primary', layoutColor[0])
    root.style.setProperty('--iris-color-secondary', layoutColor[2])
}