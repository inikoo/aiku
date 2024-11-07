/**
 *  author: Vika Aqordi
 *  created on: 18-10-2024
 *  github: https://github.com/aqordeon
 *  copyright: 2024
*/

export const checkVisible = (visible: string | null, isLoggedIn: boolean) => {
    if (!visible) return true

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