import React, {useContext} from 'react';
import {Text, View, Button} from 'react-native';
import {AuthContext} from '@/src/components/Context/context';

const Home = () => {
  const {userData} = useContext(AuthContext);
  console.log('redux',useContext(AuthContext))

  return (
    <View className='flex-1 justify-center items-center'>
      <Text className="text-2xl text-purple-500 font-bold">Home</Text>
      <Text className="text-red-500">Welcome, {userData.username}!</Text>
      <Text className="text-gray-700">Your Email: {userData.email}</Text>
      <Text className="text-gray-700">Your Role: {userData.role}</Text>
      <Text className="text-gray-700">Your ID: {userData.id}</Text>
    </View>
  );
};

export default Home;
