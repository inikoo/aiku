export interface Navigation {
    [key: string]: {
        title: string
        icon: string | string[]
        type?: string
        align?: string
        iconClass?: string
    }
}

export interface Tabs {
    current: string
    navigation: Navigation
}