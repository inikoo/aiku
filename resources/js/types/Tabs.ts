export interface Tabs {
    current: string
    navigation: {
        pallets: {
            title: string
            icon: string | string[]
        },
        history: {
            title: string
            icon: string | string[]
            type: string
            align: string
        }
    }
}