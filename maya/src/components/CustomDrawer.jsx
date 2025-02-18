import React, {useContext, useState} from 'react';
import {View, Text, Image, TouchableOpacity} from 'react-native';
import {
  DrawerContentScrollView,
  DrawerItemList,
} from '@react-navigation/drawer';
import {AuthContext} from '@/src/components/Context/context';

import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {faSignOutAlt} from '@/private/fa/pro-regular-svg-icons';
import {faWarehouse} from '@/private/fa/pro-regular-svg-icons';
import { logout } from '@/src/user';

const CustomDrawer = props => {
  const {signOut, userData, warehouse} = useContext(AuthContext);
  const [imageError, setImageError] = useState(false);
  const handleImageError = () => setImageError(true);
  const getInitials = name => {
    if (!name) return '?';
    return name
      .split(' ')
      .map(word => word[0])
      .join('')
      .toUpperCase();
  };

  return (
    <View className="flex-1">
      <DrawerContentScrollView
        {...props}
        contentContainerClassName="p-0 bg-[#6366F1]">
        <View className="p-5 bg-indigo-500 flex items-center">
          <TouchableOpacity
            onPress={() => props.navigation.navigate('setting')}>
            {!imageError && userData?.image?.original ? (
              <Image
                source={{uri: userData.image.original}}
                className="w-20 h-20 rounded-full mb-2"
                onError={handleImageError}
              />
            ) : (
              <View className="w-20 h-20 rounded-full bg-gray-300 flex items-center justify-center mb-2 bg-white">
                <Text className="text-xl font-bold text-gray-700">
                  {getInitials(userData?.username)}
                </Text>
              </View>
            )}
          </TouchableOpacity>
          <Text className="text-white text-lg font-medium mb-1">
            {userData?.username || 'Guest User'}
          </Text>
        </View>

        {warehouse ? (
          <View className="bg-white border border-gray-300 rounded-md m-2 relative mt-10">
            <View className="absolute top-0 left-0 w-full h-[2px] bg-gray-300" />

            <View className="absolute top-[-10px] left-4 bg-white px-2 flex flex-row items-center gap-2">
              <FontAwesomeIcon icon={faWarehouse} size={18} />
              <Text className="text-lg font-bold">{warehouse.name}</Text>
            </View>

            <View className="pt-6 px-2">
              <DrawerItemList {...props} contentContainerStyle={{padding: 0}} />
            </View>
          </View>
        ) : (
          <View className="bg-white pt-2">
            <DrawerItemList {...props} contentContainerStyle={{padding: 0}} />
          </View>
        )}
      </DrawerContentScrollView>

      <View className="p-5 border-t border-gray-300">
        <TouchableOpacity
          onPress={()=>logout(signOut)}
          className="py-3 flex-row items-center">
          <FontAwesomeIcon icon={faSignOutAlt} size={22} />
          <Text className="text-base font-medium ml-2">Sign Out</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
};

export default CustomDrawer;
