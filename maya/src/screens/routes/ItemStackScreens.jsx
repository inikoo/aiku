import React, {useContext, useEffect, useState} from 'react';
import {View, ActivityIndicator} from 'react-native';
import BottomTabs from '@/src/components/BottomTabs';
import {AuthContext} from '@/src/components/Context/context';
import Empty from '@/src/components/Empty';
import request from '@/src/utils/Request';
import {ALERT_TYPE, Toast} from 'react-native-alert-notification';

import ShowStoredItem from '@/src/screens/StoredItem/ShowStoredItem';
import PalletsInStoredItem from '@/src/screens/StoredItem/PalletsInStoredItem';
import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {
    faPallet,
    faNarwhal,
} from '@/private/fa/pro-regular-svg-icons';

const ItemStackScreen = ({navigation, route}) => {
    const {organisation, warehouse} = useContext(AuthContext);
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const {id} = route.params;

    const getDataFromServer = async () => {
        setLoading(true);
        request({
            urlKey: 'get-stored-item',
            args: [organisation.id, warehouse.id, id],
            onSuccess: response => {
                setData(response.data);
                setLoading(false);
            },
            onFailed: error => {
                setLoading(false);
                Toast.show({
                    type: ALERT_TYPE.DANGER,
                    title: 'Error',
                    textBody: error.detail?.message || 'Failed to fetch data',
                });
            },
        });
    };

    useEffect(() => {
        getDataFromServer();
    }, [id]);

    if (loading) {
        return (
            <View className="flex-1 items-center justify-center bg-gray-100">
                <ActivityIndicator size="large" color="#3b82f6" />
            </View>
        );
    }

    return (
        <>
            {data ? (
                <BottomTabs
                    tabArr={[
                        {
                            route: 'show-items',
                            label: 'SKU',
                            icon: faNarwhal,
                            component: () => <ShowStoredItem data={data} />,
                        },
                        {
                            route: 'pallets-in-item',
                            label: 'Pallet',
                            icon: faPallet,
                            component: () => (
                                <PalletsInStoredItem data={data} />
                            ),
                        },
                    ]}
                />
            ) : (
                <Empty />
            )}
        </>
    );
};

export default ItemStackScreen;
