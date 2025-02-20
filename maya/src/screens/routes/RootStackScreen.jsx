import React from 'react';
import { createStackNavigator } from '@react-navigation/stack';

import login from '@/src/screens/LoginScreen';
import ScannerLogin from '@/src/screens/ScannerLogin';

const RootStack = createStackNavigator();

const RootStackScreen = ({navigation}) => (
    <RootStack.Navigator screenOptions={{ headerShown: false }}>
        <RootStack.Screen name="login" component={login}/>
        <RootStack.Screen name="scanner-login" component={ScannerLogin}/>
    </RootStack.Navigator>
);

export default RootStackScreen;