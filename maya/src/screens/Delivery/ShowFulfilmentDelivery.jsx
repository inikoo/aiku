import React, {useState} from 'react';
import {View, ScrollView, TouchableOpacity} from 'react-native';
import {Card} from '@/src/components/ui/card';
import {Heading} from '@/src/components/ui/heading';
import {Center} from '@/src/components/ui/center';
import {Text} from '@/src/components/ui/text';
import dayjs from 'dayjs';
import globalStyles from '@/globalStyles';
import Barcode from 'react-native-barcode-svg';
import Timeline from 'react-native-timeline-flatlist';
import Description from '@/src/components/Description';
import {useSafeAreaInsets} from 'react-native-safe-area-context';
import {useDelivery} from '@/src/components/Context/delivery';
import {getFilteredActionsDelivery} from '@/src/utils';
import SetStateButton from '@/src/components/SetStateButton';
import {Alert, AlertText} from '@/src/components/ui/alert';

import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {faChevronDown, faChevronUp,faCheckCircle} from '@/private/fa/pro-light-svg-icons';

const ShowFulfilmentDelivery = ({navigation, route, onChangeState}) => {
  const {data} = useDelivery();
  const [isTimelineOpen, setIsTimelineOpen] = useState(false);
  const insets = useSafeAreaInsets();

  if (!data) {
    return (
      <View className="flex-1 items-center justify-center bg-gray-100">
        <Text className="text-lg text-gray-600">No Data Available</Text>
      </View>
    );
  }

  const timelineData = data.timeline
    ? Object.values(data.timeline).map(event => {
        const formattedTime = dayjs(event.timestamp).isValid()
          ? dayjs(event.timestamp).format('HH:mm')
          : 'N/A';
        const formattedDate = dayjs(event.timestamp).isValid()
          ? dayjs(event.timestamp).format('YYYY-MM-DD HH:mm')
          : 'N/A';

        return {
          time: formattedTime,
          title: event.label,
          description: formattedDate,
          /*   lineColor: event.label === data.state_label ? '#66DC71' : 'gray', */
          circleColor: event.label === data.state_label ? '#66DC71' : 'gray',
        };
      })
    : [];

  const schema = [
    {
      label: 'Customer',
      value: data.customer_name,
    },
    {
      label: 'Boxes',
      value: data.number_boxes ? data.number_boxes.toString() : '-',
    },
    {
      label: 'Oversizes',
      value: data.number_oversizes ? data.number_oversizes.toString() : '-',
    },
    {
      label: 'Pallets',
      value: data.number_pallets ? data.number_pallets.toString() : '-',
    },
    {
      label: 'Services',
      value: data.number_services ? data.number_services.toString() : '-',
    },
    {
      label: 'Estimated delivery',
      value: data.estimated_delivery_date
        ? dayjs(data.estimated_delivery_date).format('MMMM D[,] YYYY')
        : 'N/A',
    },
    {
      label: 'Note',
      value: data.public_notes,
    },
  ];
console.log(data)

  return (
    <ScrollView
      style={globalStyles.container}
      contentContainerStyle={{paddingBottom: insets.bottom + 120}}>
      {data.state != "Booked-in" ? (
        <SetStateButton
          button1={{
            size: 'md',
            variant: 'outline',
            action: 'primary',
            style: {borderTopRightRadius: 0, borderBottomRightRadius: 0},
            onPress: null,
            text: `To do : 1 / ${data.number_pallets || 0}`,
          }}
          button2={{
            size: 'md',
            action: 'primary',
            style: {borderTopLeftRadius: 0, borderBottomLeftRadius: 0},
            onPress: () =>
              onChangeState(getFilteredActionsDelivery(data.state).id),
            text: 'Set to ' + getFilteredActionsDelivery(data.state).title,
          }}
        />
      ) : (
        <Alert action="success" variant="solid">
          <FontAwesomeIcon icon={faCheckCircle} color="green" />
          <AlertText>Already Booked In</AlertText>
        </Alert>
      )}

      <Card className="mt-4">
        <Heading>Delivery Details</Heading>
        <Description schema={schema} />
      </Card>

      <Card className="mt-4">
        <Center>
          <Barcode
            value={data.reference}
            format="CODE128"
            maxWidth={250}
            height={60}
          />
        </Center>
        <Center>
          <Heading>{data.reference}</Heading>
        </Center>
      </Card>

      <Card className={`mt-4 p-2 ${!isTimelineOpen ? 'bg-indigo-300' : ''}`}>
        <TouchableOpacity
          className={`flex-row justify-between items-center py-2 px-4`}
          onPress={() => setIsTimelineOpen(!isTimelineOpen)}>
          <View>
            {!isTimelineOpen && (
              <View className="flex-row items-center mt-2 gap-4 ">
                <FontAwesomeIcon
                  icon={data.state_icon.icon}
                  color={data.state_icon.color}
                  size={23}
                />
                <Text className="ml-2 font-bold text-white">
                  {data.state_label}
                </Text>
              </View>
            )}
          </View>
          <FontAwesomeIcon
            icon={isTimelineOpen ? faChevronUp : faChevronDown}
            size={20}
          />
        </TouchableOpacity>
        <View className="px-4">
          {isTimelineOpen && timelineData.length > 0 && (
            <Timeline
              data={timelineData}
              lineColor="gray"
              circleColor="#66DC71"
              innerCircle={'dot'}
              isUsingFlatlist={false}
              timeStyle={{
                textAlign: 'center',
                backgroundColor: '#7C86FF',
                color: 'white',
                paddingHorizontal: 4,
                paddingVertical: 2,
                borderRadius: 13,
              }}
              descriptionStyle={{color: 'gray'}}
            />
          )}
        </View>
      </Card>
    </ScrollView>
  );
};

export default ShowFulfilmentDelivery;
