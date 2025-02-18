import React, {useContext, useRef, useState} from 'react';
import {
  View,
  TouchableOpacity,
  Text,
  Animated,
  PanResponder,
} from 'react-native';
import {useForm, Controller} from 'react-hook-form';
import {AuthContext} from '@/src/components/Context/context';
import BaseList from '@/src/components/BaseList';
import globalStyles from '@/globalStyles';
import {ALERT_TYPE, Toast} from 'react-native-alert-notification';
import {Button, ButtonText, ButtonSpinner} from '@/src/components/ui/button';
import {Input, InputField} from '@/src/components/ui/input';
import Modal from '@/src/components/Modal';
import request from '@/src/utils/Request';
import {
  Radio,
  RadioGroup,
  RadioIndicator,
  RadioLabel,
  RadioIcon,
} from '@/src/components/ui/radio';
import {Textarea, TextareaInput} from '@/src/components/ui/textarea';

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
const Pallet = ({navigation}) => {
  const {organisation, warehouse} = useContext(AuthContext);

  return (
    <View style={globalStyles.container}>
      <BaseList
        navigation={navigation}
        urlKey="get-pallets"
        args={[organisation.id, warehouse.id]}
        listItem={({item, navigation}) => (
          <GroupItem item={item} navigation={navigation} />
        )}
      />
    </View>
  );
};

const GroupItem = ({item: initialItem, navigation}) => {
  const [item, setItem] = useState(initialItem);
  const [showModalMovePallet, setShowModalMovePallet] = useState(false);
  const [loadingSave, setLoadingSave] = useState(false);
  const translateX = useRef(new Animated.Value(0)).current;
  const [showModalDamaged, setShowModalDamaged] = useState(false);
  const SWIPE_THRESHOLD = 60;
  const MAX_SWIPE = 100;
  const {control, handleSubmit, reset, setValue} = useForm({
    defaultValues: {
      status: '',
      notes: '',
      location: '',
    },
  });

  const panResponder = useRef(
    PanResponder.create({
      onStartShouldSetPanResponder: () => true,
      onMoveShouldSetPanResponder: (_, gestureState) => Math.abs(gestureState.dx) > 5,
      onPanResponderMove: (_, gestureState) => {
        if (Math.abs(gestureState.dx) > 100) {
          translateX.setValue(
            Math.min(Math.max(gestureState.dx, -MAX_SWIPE), MAX_SWIPE),
          );
        }
      },
      onPanResponderRelease: (_, gestureState) => {
        if (gestureState.dx > SWIPE_THRESHOLD) {
          Animated.spring(translateX, {
            toValue: MAX_SWIPE,
            useNativeDriver: true,
          }).start();
        } else if (gestureState.dx < -SWIPE_THRESHOLD) {
          Animated.spring(translateX, {
            toValue: -MAX_SWIPE,
            useNativeDriver: true,
          }).start();
        } else {
          Animated.spring(translateX, {
            toValue: 0,
            useNativeDriver: true,
          }).start();
        }
      },
    }),
  ).current;
  

  const onSubmitSetLocation = formData => {
    request({
      method: 'patch',
      urlKey: 'set-pallet-location',
      args: [formData.location, item.id],
      data: formData,
      onSuccess: response => {
        setItem(prevItem => ({
          ...prevItem,
          location_code: response.data.location_code,
        }));

        Animated.spring(translateX, {
          toValue: 0,
          useNativeDriver: true,
        }).start();

        setShowModalMovePallet(false);

        Toast.show({
          type: ALERT_TYPE.SUCCESS,
          title: 'Success',
          textBody: 'Updated pallet ' + item.reference,
        });
      },
      onFailed: error => {
        setShowModalMovePallet(false);
        Toast.show({
          type: ALERT_TYPE.DANGER,
          title: 'Error',
          textBody:
            error.detail?.message ||
            'Failed to update pallet ' + item.reference,
        });
      },
    });
  };

  const onSubmitSetDamaged = async formData => {
    console.log(formData);
  };

  return (
    <View style={{marginVertical: 5}}>
      <View style={[{width: MAX_SWIPE}, globalStyles.button_swipe_primary]}>
        <TouchableOpacity
          size="md"
          variant="solid"
          onPress={() => setShowModalMovePallet(true)}>
          <FontAwesomeIcon icon={faInventory} size={25} color="#615FFF" />
        </TouchableOpacity>
      </View>

      <View style={[{width: MAX_SWIPE}, globalStyles.button_swipe_danger]}>
        <TouchableOpacity size="md" variant="solid" onPress={() => setShowModalDamaged(true)}>
          <FontAwesomeIcon icon={faFragile} color="red" size={25}  />
        </TouchableOpacity>
      </View>

      <Animated.View
        {...panResponder.panHandlers}
        pointerEvents="box-none"
        style={[
          {
            transform: [{translateX}],
            shadowColor: '#000',
            shadowOffset: {width: 0, height: 2},
            shadowOpacity: 0.2,
            shadowRadius: 4,
          },
          globalStyles.list.card,
        ]}>
        <TouchableOpacity
          activeOpacity={0.7}
          pointerEvents="auto"
          onPress={() => navigation.navigate('show-pallet', {id: item.id})}>
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
              <Text style={globalStyles.list.title}>
                {item?.reference || 'No reference available'}
              </Text>
              <Text style={globalStyles.list.description}>
                {item?.customer_reference || 'No customer reference available'}
              </Text>
            </View>

            <View
              style={{
                marginLeft: 10,
                flexDirection: 'row',
                alignItems: 'center',
                gap: 5,
              }}>
              <FontAwesomeIcon
                icon={faInventory}
                size={20}
                style={{marginRight: 5}}
              />
              <Text style={{fontWeight: 'bold'}}>
                {item?.location_code || '-'}
              </Text>
            </View>
          </View>
        </TouchableOpacity>
      </Animated.View>

      <Modal
        isVisible={showModalMovePallet}
        title="Move Pallet"
        onClose={() => setShowModalMovePallet(false)}>
        <View className="w-full">
          <Text className="text-sm font-semibold mb-1">Location</Text>
          <Controller
            name="location"
            control={control}
            render={({field}) => (
              <Input variant="outline" size="md">
                <InputField
                  placeholder="Enter new location..."
                  value={field.value}
                  onChangeText={field.onChange}
                />
              </Input>
            )}
          />

          <Button
            size="lg"
            className="my-3"
            onPress={handleSubmit(onSubmitSetLocation)}>
            {loadingSave ? (
              <ButtonSpinner />
            ) : (
              <View
                style={{flexDirection: 'row', alignItems: 'center', gap: 8}}>
                <FontAwesomeIcon icon={faSave} size={20} color="#fff" />
                <ButtonText>Move Pallet</ButtonText>
              </View>
            )}
          </Button>
        </View>
      </Modal>

      <Modal
        isVisible={showModalDamaged}
        title="Set Damaged"
        onClose={() => setShowModalDamaged(false)}>
        <View className="w-full">
          <Text className="text-sm font-semibold mb-1">Status</Text>
          <Controller
            name="status"
            control={control}
            render={({field}) => (
              <RadioGroup
                value={field.value}
                onChange={field.onChange}
                className="my-2">
                <View className="flex-row gap-4">
                  <Radio value="damaged">
                    <RadioIndicator>
                      <RadioIcon as={CircleIcon} />
                    </RadioIndicator>
                    <RadioLabel>Damaged</RadioLabel>
                  </Radio>

                  <Radio value="lost">
                    <RadioIndicator>
                      <RadioIcon as={CircleIcon} />
                    </RadioIndicator>
                    <RadioLabel>Lost</RadioLabel>
                  </Radio>
                </View>
              </RadioGroup>
            )}
          />

          <Text className="text-sm font-semibold mt-4 mb-1">Notes</Text>
          <Controller
            name="notes"
            control={control}
            render={({field}) => (
              <Textarea className="my-2">
                <TextareaInput
                  placeholder="Additional notes..."
                  value={field.value}
                  onChangeText={field.onChange}
                />
              </Textarea>
            )}
          />

          <Button size="lg" onPress={handleSubmit(onSubmitSetDamaged)}>
            {loadingSave ? (
              <ButtonSpinner />
            ) : (
              <View
                style={{flexDirection: 'row', alignItems: 'center', gap: 8}}>
                <FontAwesomeIcon icon={faSave} size={20} color="#fff" />
                <ButtonText>Save</ButtonText>
              </View>
            )}
          </Button>
        </View>
      </Modal>
    </View>
  );
};

export default Pallet;
