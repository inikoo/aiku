import React, {useRef, useImperativeHandle, forwardRef} from 'react';
import {View, StyleSheet} from 'react-native';
import {MenuView} from '@react-native-menu/menu';

const Menu = forwardRef(({actions, button, onPressAction}, ref) => {
  const menuRef = useRef(null);

  useImperativeHandle(ref, () => ({
    menu: menuRef.current,
  }));

  return (
    <View style={styles.container}>
      {button}
      <MenuView
        ref={menuRef}
        title="Menu Title"
        onPressAction={onPressAction}
        actions={actions}
        shouldOpenOnLongPress={false}></MenuView>
    </View>
  );
});

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  loadingContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#f3f4f6',
  },
  noDataText: {fontSize: 18, color: '#6b7280'},
  cardMargin: {marginTop: 16},
});

export default Menu;
