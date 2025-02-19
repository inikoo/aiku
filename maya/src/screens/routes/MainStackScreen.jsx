import React, {useContext, useEffect, useState} from 'react';
import {createDrawerNavigator} from '@react-navigation/drawer';
import {createStackNavigator} from '@react-navigation/stack';
import CustomDrawer from '@/src/components/CustomDrawer';
import {AuthContext} from '@/src/components/Context/context';

import Home from '@/src/screens/Home';
import InventoryStackScreen from '@/src/screens/routes/InventoryStackScreen';
import GoodsInStackScreen from '@/src/screens/routes/GoodsInStackScreen';
import GoodsOutStackScreen from '@/src/screens/routes/GoodsOutStackScreen';
import LocationStackScreen from '@/src/screens/routes/LocationStackScreen';
import DeliveryStackScreen from '@/src/screens/routes/DeliveryStackScreens';
import RetrunStackScreen from '@/src/screens/routes/RetrunsStackScreens';
import PalletStackScreen from '@/src/screens/routes/PalletStackScreen';
import ItemStackScreens from '@/src/screens/routes/ItemStackScreens'
import Settings from '@/src/screens/Settings';
import Organisation from '@/src/screens/Organisation';
import Warehouse from '@/src/screens/Warehouse'
import Fulfilment from '@/src/screens/Fulfilment';
import ShowDeliveryNote from '@/src/screens/DeliveryNote/ShowDeliveryNote';
import ShowLocation from '@/src/screens/Location/ShowLocation';
import ShowArea from '@/src/screens/Area/ShowArea';
import ShowStockDelivery from '@/src/screens/Stock/ShowStockDelivery';
import ShowStoredItem from '@/src/screens/StoredItem/ShowStoredItem';
import SessionExpired from '@/src/screens/SessionExpired';
import ShowOrgStock from '@/src/screens/OrgStock/ShowOrgStock';
import EditProfile from '@/src/screens/EditProfile';
import Scanner from '@/src/screens/Scanner';

import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {
    faHome,
    faPalletAlt,
    faInventory,
    faArrowToBottom,
    faArrowFromLeft,
    faUsers,
    faBarcodeScan,
    faWarehouseAlt,
} from '@/private/fa/pro-regular-svg-icons';
import {faHandHoldingBox} from '@/private/fa/pro-regular-svg-icons';
import {TouchableOpacity} from 'react-native';
import {useNavigation} from '@react-navigation/native';

const Drawer = createDrawerNavigator();
const Stack = createStackNavigator();

const DrawerNavigator = () => {
    const {userData, organisation, warehouse, fulfilment} = useContext(AuthContext);
    const navigation = useNavigation();
    const [permission, setPermission] = useState([]);

    useEffect(() => {
        const permissionList = [
            `fulfilment-shop.${fulfilment?.id}`,
            `fulfilment-shop.${fulfilment?.id}.view`,
            `fulfilment.${warehouse?.id}`,
            `supervisor-fulfilment-shop.${warehouse?.id}`,
        ];
        setPermission(permissionList);
    }, [userData]);

    const MenuScreens = [
        {
            name: 'Home',
            component: Home,
            permission: [],
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon icon={faHome} size={22} color={color} />
                ),
                headerRight: () => (
                    <TouchableOpacity
                        className="mx-3"
                        onPress={() => navigation.navigate('scanner')}>
                        <FontAwesomeIcon icon={faBarcodeScan} size={22} />
                    </TouchableOpacity>
                ),
            },
        },
        {
            name: 'Inventory',
            permission: permission,
            component: InventoryStackScreen,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon
                        icon={faInventory}
                        size={22}
                        color={color}
                    />
                ),
                headerRight: () => (
                    <TouchableOpacity
                        className="mx-3"
                        onPress={() => navigation.navigate('scanner')}>
                        <FontAwesomeIcon icon={faBarcodeScan} size={22} />
                    </TouchableOpacity>
                ),
            },
        },
        {
            name: 'Location',
            permission: permission,
            component: LocationStackScreen,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon
                        icon={faPalletAlt}
                        size={22}
                        color={color}
                    />
                ),
                headerRight: () => (
                    <TouchableOpacity
                        className="mx-3"
                        onPress={() => navigation.navigate('scanner')}>
                        <FontAwesomeIcon icon={faBarcodeScan} size={22} />
                    </TouchableOpacity>
                ),
            },
        },
        {
            name: 'Goods In',
            permission: permission,
            component: GoodsInStackScreen,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon
                        icon={faArrowToBottom}
                        size={22}
                        color={color}
                    />
                ),
                headerRight: () => (
                    <TouchableOpacity
                        className="mx-3"
                        onPress={() => navigation.navigate('scanner')}>
                        <FontAwesomeIcon icon={faBarcodeScan} size={22} />
                    </TouchableOpacity>
                ),
            },
        },
        {
            name: 'Goods Out',
            permission: permission,
            component: GoodsOutStackScreen,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon
                        icon={faArrowFromLeft}
                        size={22}
                        color={color}
                    />
                ),
                headerRight: () => (
                    <TouchableOpacity
                        className="mx-3"
                        onPress={() => navigation.navigate('scanner')}>
                        <FontAwesomeIcon icon={faBarcodeScan} size={22} />
                    </TouchableOpacity>
                ),
            },
        },
    ];

    const organisationScreens = [
        {
            name: 'Home',
            component: Home,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon icon={faHome} size={22} color={color} />
                ),
            },
        },
        {
            name: 'Organisation',
            component: Organisation,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon icon={faUsers} size={22} color={color} />
                ),
            },
        },
        {
            name: 'Fulfilment',
            component: Fulfilment,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon
                        icon={faHandHoldingBox}
                        size={22}
                        color={color}
                    />
                ),
            },
        },
        {
          name: 'Warehouse',
          component: Warehouse,
          options: {
              drawerIcon: ({color}) => (
                  <FontAwesomeIcon
                      icon={faWarehouseAlt}
                      size={22}
                      color={color}
                  />
              ),
          },
      },
    ];

    return (
        <Drawer.Navigator
            drawerContent={props => <CustomDrawer {...props} />}
            screenOptions={{
                headerShown: true,
                drawerActiveBackgroundColor: '#fff',
                drawerActiveTintColor: '#4F46E5',
                drawerInactiveTintColor: '#333',
            }}>
            {warehouse
                ? MenuScreens.filter(
                      ({permission}) =>
                          permission.length === 0 ||
                          permission.some(perm =>
                              userData?.permissions?.includes(perm),
                          ),
                  ).map(({name, component, options}) => (
                      <Drawer.Screen
                          key={name}
                          name={name}
                          component={component}
                          options={options}
                      />
                  ))
                : organisationScreens
                      .filter(({name}) => name !== 'Fulfilment' || organisation)
                      .map(({name, component, options}) => (
                          <Drawer.Screen
                              key={name}
                              name={name}
                              component={component}
                              options={options}
                          />
                      ))}
        </Drawer.Navigator>
    );
};

const HomeStack = () => {
    return (
        <Stack.Navigator
            screenOptions={{
                headerShown: false,
                /*     headerStyle: { backgroundColor: '#4F46E5' },
        headerTintColor: '#fff', */
            }}>
            <Stack.Screen name="home-drawer" component={DrawerNavigator} />
            <Stack.Screen name="setting" component={Settings} />
            <Stack.Screen
                name="show-pallet"
                component={PalletStackScreen}
                options={{
                    headerShown: true,
                    title: 'Pallet',
                }}
            />
             <Stack.Screen
                name="show-fulfilment-delivery"
                component={DeliveryStackScreen}
                options={{
                    headerShown: true,
                    title: 'Fulfilment Delivery',
                }}
            />
            <Stack.Screen
                name="show-fulfilment-return"
                component={RetrunStackScreen}
                options={{
                    headerShown: true,
                    title: 'Fulfilment Returns',
                }}
            />
            <Stack.Screen
                name="organisation"
                component={Organisation}
                options={{
                    headerShown: true,
                    title: 'Organisation',
                }}
            />
            <Stack.Screen
                name="fulfilment"
                component={Fulfilment}
                options={{
                    headerShown: true,
                    title: 'Fulfilment',
                }}
            />
            <Stack.Screen
                name="warehouse"
                component={Warehouse}
                options={{
                    headerShown: true,
                    title: 'Warehouse',
                }}
            />
            {/*  inventory */}
            <Stack.Screen
                name="show-delivery-note"
                component={ShowDeliveryNote}
                options={{
                    headerShown: true,
                    title: 'Delivery Note',
                }}
            />
            <Stack.Screen
                name="show-location"
                component={ShowLocation}
                options={{
                    headerShown: true,
                    title: 'Location',
                }}
            />
            <Stack.Screen
                name="show-area"
                component={ShowArea}
                options={{
                    headerShown: true,
                    title: 'Area',
                }}
            />
            <Stack.Screen
                name="show-stock-delivery"
                component={ShowStockDelivery}
                options={{
                    headerShown: true,
                    title: 'Stock Delivery',
                }}
            />
            <Stack.Screen
                name="show-stored-item"
                component={ItemStackScreens}
                options={{
                    headerShown: true,
                    title: 'SKU',
                }}
            />
            <Stack.Screen
                name="scanner"
                component={Scanner}
                options={{
                    /*  headerShown: true, */
                    title: 'scanner',
                }}
            />
            <Stack.Screen
                name="show-org-stock"
                component={ShowOrgStock}
                options={{
                    headerShown: true,
                    title: 'OrgStock',
                }}
            />
            <Stack.Screen
                name="session-expired"
                component={SessionExpired}
                options={{
                    headerShown: false,
                    title: 'Expired Token',
                }}
            />
            <Stack.Screen
                name="edit-profile"
                component={EditProfile}
                options={{
                    headerShown: true,
                    title: 'Edit Profile',
                }}
            />
        </Stack.Navigator>
    );
};

export default HomeStack;
