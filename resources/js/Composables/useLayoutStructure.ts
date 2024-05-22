import { useColorTheme } from '@/Composables/useStockList'

import { OrganisationsData, Group, OrganisationState, StackedComponent} from '@/types/LayoutRules'
import { Colors } from "@/types/Color"
import { Navigation, grpNavigation, orgNavigation } from "@/types/Navigation"
import { Image } from "@/types/Image"

export const layoutStructure = {
    agents: {
        // currentOrganisations: '',
        data: {} as OrganisationsData[]
    },
    agentsState: {} as {[key: string]: OrganisationState},
    app: {
        name: "",  // For styling navigation depend on which App
        color: null as unknown | Colors,  // Styling layout color
        theme: useColorTheme[0] as string[],  // For styling app color
        url: null as string | null, // For url on logo top left
    },
    currentModule: "",
    currentRoute: "grp.dashboard.show", // Define value to avoid route null at first load
    currentParams: {} as {[key: string]: string},
    digital_agency: {} as {data: OrganisationsData[]},
    group: null as Group | null,
    leftSidebar: {
        show: true,
    },
    navigation: {
        grp: {} as grpNavigation,
        org: {} as { [key: string]: orgNavigation } | { [key: string]: Navigation } | Navigation
    },
    organisations: {
        data: {} as OrganisationsData[]
    },
    organisationsState: {} as {[key: string]: OrganisationState},
    rightSidebar: {
        activeUsers: {
            users: [],
            count: 0,
            show: false
        },
        language: {
            show: false
        }
    },
    stackedComponents: [] as StackedComponent[],
    user: {} as { id: number, avatar_thumbnail: Image, email: string, username: string },
}