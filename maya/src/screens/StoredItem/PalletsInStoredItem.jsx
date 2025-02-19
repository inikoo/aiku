import React from 'react';
import {View, FlatList, TouchableOpacity, Text} from 'react-native';
import globalStyles from '@/globalStyles';
import Empty from '@/src/components/Empty';

import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {library} from '@fortawesome/fontawesome-svg-core';
import {CircleIcon} from '@/src/components/ui/icon';
import {
    faSeedling,
    faShare,
    faSpellCheck,
    faCheck,
    faCross,
    faCheckDouble,
    faWarehouseAlt,
    faPallet,
    faTimes,
    faFragile,
} from '@/private/fa/pro-light-svg-icons';
import {
    faFileInvoiceDollar,
    faInventory,
    faTimes as faTimesRegular,
    faHistory,
    faSave,
} from '@/private/fa/pro-regular-svg-icons';

library.add(
    faSeedling,
    faShare,
    faSpellCheck,
    faCheck,
    faCross,
    faCheckDouble,
    faWarehouseAlt,
    faPallet,
    faTimes,
    faFileInvoiceDollar,
);

const PalletsInItem = ({navigation, route, data}) => {
    return (
        <View style={globalStyles.container}>
            <FlatList
                data={data.pallets}
                keyExtractor={item => item.id.toString()}
                showsVerticalScrollIndicator={false}
                ListEmptyComponent={<Empty />}
                renderItem={({item}) => (
                    <GroupItem item={item} navigation={navigation} />
                )}
            />
        </View>
    );
};

const GroupItem = ({item, navigation}) => {
    return (
        <View pointerEvents="box-none" style={globalStyles.list.card}>
            <TouchableOpacity activeOpacity={0.7} pointerEvents="auto">
                <View style={globalStyles.list.container}>
                    <View style={globalStyles.list.avatarContainer}>
                        {item?.state_icon && (
                            <FontAwesomeIcon
                                icon={item.state_icon.icon}
                                size={24}
                                color={item.state_icon.color}
                                style={{marginVertical: 3}}
                            />
                        )}
                        {item?.type_icon && (
                            <FontAwesomeIcon
                                icon={item.type_icon.icon}
                                size={24}
                                style={{marginVertical: 3}}
                            />
                        )}
                    </View>

                    <View style={globalStyles.list.textContainer}>
                        <View className="flex-row justify-between">
                            <Text style={globalStyles.list.title}>
                                {item?.customer_reference || 'N/A'}
                            </Text>
                        </View>

                        <Text style={globalStyles.list.description}>
                            {item?.reference}
                        </Text>
                    </View>
                </View>
            </TouchableOpacity>
        </View>
    );
};

export default PalletsInItem;
