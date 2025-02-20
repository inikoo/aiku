import React, {useContext} from 'react';
import {Text, View, TouchableOpacity} from 'react-native';
import {AuthContext} from '@/src/components/Context/context';
import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {faSignIn, faSignOut} from '@/private/fa/pro-solid-svg-icons';
import {Button, ButtonText} from '@/src/components/ui/button';
import {logout} from '@/src/user';

const SessionExpired = () => {
  const {userData, signOut} = useContext(AuthContext);

  return (
    <View className="flex-1 justify-center items-center bg-white px-6">
      {/* Icon Session Expired */}
      <FontAwesomeIcon icon={faSignIn} size={50} color="#433CC3" />

      {/* Teks Informasi */}
      <Text className="text-2xl text-indigo-500 font-bold mt-4">
        Your session has expired
      </Text>
      <Text className="text-gray-600 text-center mt-2">
        Please log in again to continue using the app.
      </Text>

      {/* Tombol Logout */}
      <Button
        size="md"
        variant="solid"
        action="primary"
        className="my-3"
        onPress={() => logout(signOut)}>
        <ButtonText>Log out</ButtonText>
      </Button>
    </View>
  );
};

export default SessionExpired;
