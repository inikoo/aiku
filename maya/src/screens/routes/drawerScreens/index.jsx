import React, { useContext } from 'react';
import { createDrawerNavigator } from '@react-navigation/drawer';
import CustomDrawer from '@/src/components/CustomDrawer';
import { AuthContext } from '@/src/components/Context/context';

import agentMenu from '@/src/screens/routes/drawerScreens/agentMenu';
import firstMenu from '@/src/screens/routes/drawerScreens/firstMenu';
import fulfilmentMenu from '@/src/screens/routes/drawerScreens/fulfilmentMenu';

const Drawer = createDrawerNavigator();

const DrawerScreens = () => {
    const { userData, organisation, warehouse, fulfilment } = useContext(AuthContext);

    const filterPermissions = (data) =>
        data.filter(({ permission }) =>
            !Array.isArray(permission) || permission.length === 0 || permission.some((perm) => userData?.permissions?.includes(perm))
        );
    
    const getMenu = () => {
        if (organisation?.type === 'shop') {
            return filterPermissions(fulfilmentMenu(fulfilment, warehouse));
        }
        if (organisation?.type === 'agent') {
            return filterPermissions(agentMenu(fulfilment, warehouse));
        }
        return [];
    };

    const showMenu = () => {
        if (organisation) {
            if (organisation.type === 'shop' && warehouse) {
                return getMenu().map(({ name, component, options }) => (
                    <Drawer.Screen key={name} name={name} component={component} options={options} />
                ));
            }
            if (organisation.type === 'shop' && !warehouse) {
                return firstMenu.map(({ name, component, options }) => (
                    <Drawer.Screen key={name} name={name} component={component} options={options} />
                ));
            }
            if (organisation.type === 'agent') {
                return getMenu().map(({ name, component, options }) => (
                    <Drawer.Screen key={name} name={name} component={component} options={options} />
                ));
            }
        }
        return firstMenu.map(({ name, component, options }) => (
            <Drawer.Screen key={name} name={name} component={component} options={options} />
        ));
    };

    return (
        <Drawer.Navigator
            drawerContent={(props) => <CustomDrawer {...props} />}
            screenOptions={{
                headerShown: true,
                drawerActiveBackgroundColor: '#fff',
                drawerActiveTintColor: '#4F46E5',
                drawerInactiveTintColor: '#333',
            }}
        >
            {showMenu()}
        </Drawer.Navigator>
    );
};

export default DrawerScreens;
