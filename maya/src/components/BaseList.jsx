import React, {forwardRef, useEffect, useState, useImperativeHandle } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  RefreshControl,
} from 'react-native';
import {Spinner} from '@/src/components/ui/spinner';
import request from '@/src/utils/Request';
import globalStyles from '@/globalStyles';
import {SearchIcon} from '@/src/components/ui/icon';
import {ALERT_TYPE, Toast} from 'react-native-alert-notification';
import {useSafeAreaInsets} from 'react-native-safe-area-context';
import Empty from '@/src/components/Empty'

import {
  Input,
  InputField,
  InputSlot,
  InputIcon,
} from '@/src/components/ui/input';
/* import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {faBarcodeScan} from '@/private/fa/pro-regular-svg-icons';
import {faTimes} from '@/private/fa/pro-light-svg-icons'; */

const BaseList = forwardRef((props, ref) => {
  const [data, setData] = useState([]);
  const [page, setPage] = useState(1);
  const [searchQuery, setSearchQuery] = useState('');
  const [isFetching, setIsFetching] = useState(true);
  const [meta, setMeta] = useState();
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const insets = useSafeAreaInsets();

  // Fetch Data
  const getDataFromServer = async (isLoadMore = false, newPage = 1) => {
    if (isLoadMore) setIsLoadingMore(true);
    else setIsFetching(true);
    request({
      urlKey: props.urlKey,
      args: props.args,
      params: {
        ...props.params,
        [props.prefix ? `${props.prefix}_perPage` : 'perPage']: 10,
        [props.prefix ? `${props.prefix}Page` : 'page']: newPage,
        ['filter[global]']: searchQuery,
      },
      onBoth: (success, response) => {
        if (success) {
          if (isLoadMore) {
            setData(prevData => [...prevData, ...response.data]);
          } else {
            setData(response.data);
          }
          setMeta(response.meta);
          /* setLastPage(response.meta.last_page); */
        } else {
          if (response?.data?.message) {
            Toast.show({
              type: ALERT_TYPE.DANGER,
              title: 'Error',
              textBody: response.data.message,
            });
          } else {
            Toast.show({
              type: ALERT_TYPE.DANGER,
              title: 'Error',
              textBody: 'Failed to fetch data',
            });
          }
        }
        setIsFetching(false);
        setIsLoadingMore(false);
      },
    });
  };

  const fetchMoreData = (isLoadMore = false) => {
    if (isLoadMore) {
      if (meta?.last_page != page) setPage(prevPage => prevPage + 1);
    } else {
      setPage(1);
      getDataFromServer(false);
    }
  };



  const handleRefresh = () => {
    setIsRefreshing(true);
    fetchMoreData(false);
    setIsRefreshing(false);
  };

  useImperativeHandle(ref, () => ({
    handleRefresh,
    showTotalResults,
    data,
    meta
  }));

  useEffect(() => {
    getDataFromServer(page > 1, page);
  }, [page]);

  useEffect(() => {
    fetchMoreData(false);
  }, []);

  // Handle Search Query
  useEffect(() => {
    fetchMoreData(false);
  }, [searchQuery]);

  return (
    <View
      style={{flex: 1}}
      contentContainerStyle={{paddingBottom: insets.bottom + props.height}}>
      <View className="py-3">
        <Input variant="outline" size="md" className="flex-row items-center">
          <InputField
            placeholder="Search..."
            value={searchQuery}
            onChangeText={setSearchQuery}
            keyboardType="default"
            autoCapitalize="none"
          />
          <InputSlot className="pr-3">
            <InputIcon as={SearchIcon} />
          </InputSlot>
        </Input>

        {/* Badge untuk jumlah hasil */}
        {props.showTotalResults(meta)}
      </View>

      {/* List Container */}
      <View style={{flex: 1, marginBottom: 60}}>
        {isFetching ? (
          // Show Loading Indicator on First Fetch
          <View
            style={{flex: 1, justifyContent: 'center', alignItems: 'center'}}>
            <Spinner size="large" />
          </View>
        ) : (
          <FlatList
            data={data}
            keyExtractor={(item, index) => item.id}
            showsVerticalScrollIndicator={false}
            onEndReached={() => fetchMoreData(true)}
            onEndReachedThreshold={1}
            ListEmptyComponent={(<Empty />)}
            refreshControl={
              <RefreshControl
                refreshing={isRefreshing}
                onRefresh={handleRefresh}
              />
            }
            ListFooterComponent={
              isLoadingMore ? (
                <View style={{paddingVertical: 10}}>
                  <Spinner size="small" />
                </View>
              ) : null
            }
            renderItem={({item}) =>
              props.listItem ? (
                props.listItem({item: item, navigation: props.navigation})
              ) : (
                <GroupItem item={item} navigation={props.navigation} />
              )
            }
          />
        )}
      </View>
    </View>
  );
});

const GroupItem = ({item, navigation}) => {
  return (
    <TouchableOpacity
      style={globalStyles.list.card}
      activeOpacity={0.7}
      onPress={() => null}>
      <View style={globalStyles.list.container}>
        <View style={globalStyles.list.textContainer}>
          <Text style={globalStyles.list.title}>{item.reference}</Text>
          <Text style={globalStyles.list.description}>
            {item.slug || 'No description available'}
          </Text>
        </View>
      </View>
    </TouchableOpacity>
  );
};

const showTotalResults = (meta) => {
  return (
    <View className="bg-indigo-500 px-4 py-1 mt-2 self-start">
      <Text className="text-white font-semibold">
        {meta?.total || 0} Results
      </Text>
    </View>
  );
};

BaseList.defaultProps = {
  urlKey: '',
  args: [],
  params: {},
  height : 120,
  showTotalResults, // ✅ Now it's defined before being used
};


export default BaseList;
