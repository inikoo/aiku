import React from 'react';
import { View } from 'react-native';
import { Card } from '@/src/components/ui/card';
import { Button, ButtonText } from '@/src/components/ui/button';
import { Heading } from '@/src/components/ui/heading';
import { Progress, ProgressFilledTrack } from '@/src/components/ui/progress';
import { Text } from '@/src/components/ui/text';
import { VStack } from '@/src/components/ui/vstack';
import { Center } from '@/src/components/ui/center';

const DefaultRenderButton2 = ({ button2 }) => (
    <Button {...button2} className="min-h-8 px-3">
        <ButtonText className="text-xs font-semibold underline">
            {button2.text}
        </ButtonText>
    </Button>
);

const SetStateButton = ({
    button1 = {
        size: 'md',
        variant: 'outline',
        action: 'primary',
        style: {},
        onPress: () => {},
        text: 'Button 1',
    },
    progress = {
        value: 40,
        size: 'lg',
        orientation: 'horizontal',
        total: 100,
    },
    button2 = {
        size: 'sm',
        variant: 'link',
        action: 'primary',
        style: {},
        onPress: () => {},
        text: 'Button 2',
    },
    renderButton2 = DefaultRenderButton2,
}) => {
    return (
        <Card className="p-5 border border-gray-200 rounded-xl shadow-lg bg-white max-w-[410px] w-full">
            <VStack space="lg">
                <Heading>Progress to do :</Heading>
                <Center>
                    <Progress
                        value={(progress.value / progress.total) * 100}
                        size={progress.size}
                        orientation={progress.orientation}
                    >
                        <ProgressFilledTrack />
                    </Progress>
                </Center>
                <View className="flex-row items-center justify-between">
                    <Text size="md" className="font-semibold px-2">
                        {progress.value} / {progress.total}
                    </Text>
                    {renderButton2({ button2 })}
                </View>
            </VStack>
        </Card>
    );
};

export default SetStateButton;
