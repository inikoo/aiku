import React from 'react';
import {View, ScrollView} from 'react-native';
import {Card} from '@/src/components/ui/card';
import {Heading} from '@/src/components/ui/heading';
import {Text} from '@/src/components/ui/text';
import Description from '@/src/components/Description';
import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {library} from '@fortawesome/fontawesome-svg-core';

import {
    faSeedling,
    faShare,
    faSpellCheck,
    faCheck,
    faCheckDouble,
    faSortSizeUp,
    faCircle,
} from '@/private/fa/pro-light-svg-icons';
library.add(
    faSeedling,
    faShare,
    faSpellCheck,
    faCheck,
    faCheckDouble,
    faSortSizeUp,
);

const ShowStoredItem = ({navigation, route, data}) => {
    const schema = [
        {
            label: 'Customer',
            value: data?.customer_name,
        },
        {
            label: 'State',
            value: (
                <View className="flex-row items-center justify-center gap-2">
                    <FontAwesomeIcon
                        icon={data?.state_icon?.icon || faCircle}
                        color={data?.state_icon?.color}
                    />
                    <Text className="text-center">{data?.state}</Text>
                </View>
            ),
        },
        {
            label: 'Location',
            value: data?.location,
        },
        {
            label: 'Quantity',
            value: data?.quantity ? data?.quantity.toString() : '0',
        },
    ];

    return (
        <ScrollView className="flex-1 bg-gray-50 p-4">
            {/* Header */}
            <Card className="bg-indigo-600 p-6 rounded-xl shadow-lg mb-5">
                <Heading className="text-white text-2xl font-bold">
                    {data.reference || 'N/A'}
                </Heading>
                <Text className="text-white text-lg font-semibold">
                    Status: {data.state.toUpperCase()}
                </Text>
            </Card>

            {/* Detail Section */}
            <Card className="bg-white p-6 rounded-xl shadow-md">
                <Heading>Details</Heading>
                <Description schema={schema} />
            </Card>
        </ScrollView>
    );
};

export default ShowStoredItem;
