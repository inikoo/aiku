import React from 'react';
import {View, ScrollView } from 'react-native';
import {Card} from '@/src/components/ui/card';
import {Heading} from '@/src/components/ui/heading';
import {Center} from '@/src/components/ui/center';
import {Text} from '@/src/components/ui/text';
import globalStyles from '@/globalStyles';
import Barcode from 'react-native-barcode-svg';
import Description from '@/src/components/Description';

import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {library} from '@fortawesome/fontawesome-svg-core';
import {faFragile, faSave} from '@/private/fa/pro-light-svg-icons';


library.add(faFragile);

const ShowPallet = ({navigation, route, data }) => {
  return (
    <ScrollView style={globalStyles.container}>
      <Card>
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

      <Card className="mt-4">
        <Heading>Delivery Details</Heading>
        <Description
          schema={[
            {label: 'Customer', value: data.customer.name},
            {label: 'Customer Reference', value: data.customer_reference},
            {label: 'Location', value: data?.location?.resource?.code || '-'},
            {label: 'State', value: data.state},
            {
              label: 'Status',
              value: (
               <View className="flex-row items-center justify-center gap-2">
                  <FontAwesomeIcon icon={data.status_icon.icon} />
                  <Text className="text-center">{data.status}</Text>
                </View>
              ),
            },
            {label: 'Notes', value: data.notes},
          ]}
        />
      </Card>

      {/* <View className="py-4 gap-3">
        <Button
          size="lg"
          action="secondary"
          onPress={() => setShowModalMovePallet(true)}>
          <ButtonText>Move Pallet</ButtonText>
        </Button>
        <Button
          size="lg"
          variant="outline"
          action="negative"
          onPress={() => setShowModalDamaged(true)}>
          <View style={{flexDirection: 'row', alignItems: 'center', gap: 8}}>
            <FontAwesomeIcon icon={faFragile} size={20} color="#ef4444" />
            <ButtonText style={{color: '#ef4444'}}>Set as Damaged</ButtonText>
          </View>
        </Button>
      </View> */}
    </ScrollView>
  );
};

export default ShowPallet;
