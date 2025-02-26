import React, {useEffect, useReducer, useMemo} from 'react';
import {View, ActivityIndicator} from 'react-native';
import {NavigationContainer} from '@react-navigation/native';
import {createNativeStackNavigator} from '@react-navigation/native-stack';
import {GluestackUIProvider} from '@/src/components/ui/gluestack-ui-provider';
import MainStackScreen from '@/src/screens/routes/MainStackScreen';
import RootStackScreen from '@/src/screens/routes/RootStackScreen';
import AsyncStorage from '@react-native-async-storage/async-storage';
import {getData} from '@/src/utils/AsyncStorage';
import {AuthContext} from '@/src/components/Context/context';
import {loginReducer} from '@/src/Reducer/loginReducer';
import {AlertNotificationRoot} from 'react-native-alert-notification';
import { navigationRef } from '@/src/utils/NavigationService';
import './global.css';

const Stack = createNativeStackNavigator();

function App(): React.JSX.Element {
  const initialLoginState = {
    isLoading: true,
    userData: null,
    userToken: null,
    organisation: null,
    fulfilment: null,
    warehouse: null,
  };
  const [loginState, dispatch] = useReducer(loginReducer, initialLoginState);

  const authContext = useMemo(
    () => ({
      signIn: async user => {
        try {
          await AsyncStorage.setItem('persist:user', JSON.stringify(user));
        } catch (e) {
          console.log('Error storing token:', e);
        }
        dispatch({
          type: 'LOGIN',
          token: user.token,
          userData: user,
          organisation: user.organisation,
          fulfilment: null,
          warehouse: null,
        });
      },
      setOrganisation: async user => {
        try {
          await AsyncStorage.setItem('persist:user', JSON.stringify(user));
        } catch (e) {
          console.log('Error storing token:', e);
        }
        dispatch({
          type: 'SET_ORGANISATION',
          token: user.token,
          userData: user,
          organisation: user.organisation,
          fulfilment: null,
          warehouse: null,
        });
      },
      setFulfilmentWarehouse: async user => {
        try {
          await AsyncStorage.setItem('persist:user', JSON.stringify(user));
        } catch (e) {
          console.log('Error storing token:', e);
        }
        dispatch({
          type: 'SET_FULFILMENT_WAREHOUSE',
          token: user.token,
          userData: user,
          organisation: user.organisation,
          fulfilment: user.fulfilment,
          warehouse: user.warehouse,
        });
      },
      signOut: async () => {
        try {
          await AsyncStorage.removeItem('persist:user');
        } catch (e) {
          console.log('Error removing token:', e);
        }
        dispatch({type: 'LOGOUT'});
      },
      userData: loginState.userData,  // ✅ Ambil dari loginState
      organisation: loginState.organisation,  // ✅ Ambil dari loginState
      fulfilment: loginState.fulfilment,  // ✅ Ambil dari loginState
      warehouse: loginState.warehouse,  // ✅ Ambil dari loginState
    }),
    [loginState],
  );
  

  useEffect(() => {
    const loadUserToken = async () => {
      try {
        const storedUser = await getData('persist:user');
        const userToken = storedUser ? storedUser.token : null;
        dispatch({
          type: 'RETRIEVE_TOKEN',
          token: userToken,
          userData: storedUser,
          organisation: storedUser?.organisation,
          fulfilment: storedUser?.fulfilment,
          warehouse: storedUser?.warehouse,
        });
      } catch (error) {
        console.error('Error retrieving token:', error);
      }
    };
    loadUserToken();
  }, []);
  

  if (loginState.isLoading) {
    return (
      <View style={{flex: 1, justifyContent: 'center', alignItems: 'center'}}>
        <ActivityIndicator size="large" />
      </View>
    );
  }


  return (
    <GluestackUIProvider>
      <AlertNotificationRoot>
        <AuthContext.Provider value={authContext}>
          <NavigationContainer ref={navigationRef}>
            {loginState.userToken !== null ? (
              <MainStackScreen />
            ) : (
              <RootStackScreen />
            )}
          </NavigationContainer>
        </AuthContext.Provider>
      </AlertNotificationRoot>
    </GluestackUIProvider>
  );
}

export default App;
