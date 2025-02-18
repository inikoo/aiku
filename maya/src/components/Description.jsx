import React from 'react';
import { View } from 'react-native';
import { Text } from '@/src/components/ui/text';

const Description = ({ schema }) => {
  return (
    <View className="border border-gray-300 rounded-lg p-2 mt-2">
      {schema.map((item, index) => (
        <View
          key={index}
          className={`flex-row items-center ${
            index !== schema.length - 1
              ? 'border-b border-gray-200 pb-2 p-2'
              : 'px-2 p-2'
          }`}
          style={{ minHeight: 50, maxHeight: 120 }} // Tambahkan batas tinggi agar teks lebih proporsional
        >
          {/* Label */}
          <View
            className="bg-gray-200 px-2 py-1 rounded"
            style={{
              flex: 1,
              alignItems: 'center',
              justifyContent: 'center', // Pusatkan teks vertikal
            }}
          >
            {typeof item.label === 'string' ? (
              <Text className="font-semibold text-center">{item.label}</Text> // Text center
            ) : (
              item.label
            )}
          </View>

          {/* Value */}
          <View
            style={{
              flex: 2,
              justifyContent: 'end', // Pusatkan teks vertikal
            }}
          >
            {typeof item.value === 'string' ? (
              <Text
                numberOfLines={3}
                ellipsizeMode="tail"
                className="text-center"
              >
                {item.value}
              </Text>
            ) : (
              item.value
            )}
          </View>
        </View>
      ))}
    </View>
  );
};

export default Description;
