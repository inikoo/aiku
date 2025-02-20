import React from 'react';
import { View } from 'react-native';
import { Card } from '@/src/components/ui/card';
import { Button, ButtonText } from '@/src/components/ui/button';

const SetStateButton = ({
  button1 = {
    size: 'md',
    variant: 'outline',
    action: 'primary',
    style: { borderTopRightRadius: 0, borderBottomRightRadius: 0 },
    onPress: null,
    text: 'Button 1',
  },
  button2 = {
    size: 'md',
    variant: 'outline',
    action: 'primary',
    style: { borderTopLeftRadius: 0, borderBottomLeftRadius: 0 },
    onPress: null,
    text: 'Button 2',
  },
}) => {
  return (
    <Card className="p-5 border border-gray-200 rounded-xl shadow-lg bg-white">
      <View className="flex-row items-center">
        {/* Button 1 */}
        <Button {...button1} className="w-1/2 min-h-12 rounded-l-lg border-r border-gray-300 justify-center items-center">
          <ButtonText className="font-semibold text-center px-3 flex-wrap">
            {button1.text}
          </ButtonText>
        </Button>

        {/* Button 2 */}
        <Button {...button2} className="w-1/2 min-h-12 rounded-r-lg justify-center items-center">
          <ButtonText className="font-semibold text-center px-3 flex-wrap">
            {button2.text}
          </ButtonText>
        </Button>
      </View>
    </Card>
  );
};

export default SetStateButton;
