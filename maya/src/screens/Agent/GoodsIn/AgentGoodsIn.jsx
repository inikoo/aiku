import React, {useContext} from 'react';
import {Text, View, Button} from 'react-native';
import {AuthContext} from '@/src/components/Context/context';
import Config from 'react-native-config';

const AgentGoodsIn = () => {
  const {userData} = useContext(AuthContext);
  console.log('redux',useContext(AuthContext))

  return (
    <View className='flex-1 justify-center items-center'>
      <Text className="text-2xl text-purple-500 font-bold">Agent Goods In</Text>
    </View>
  );
};

export default AgentGoodsIn;
