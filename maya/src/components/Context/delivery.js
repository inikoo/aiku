import React, { createContext, useContext, useReducer } from 'react';

const DeliveryContext = createContext();

const deliveryReducer = (state, action) => {
  switch (action.type) {
    case 'SET_DATA':
      console.log('Updated Data:', action.payload); // Debugging
      return { 
        ...state, 
        data: state.data ? { ...state.data, ...action.payload } : action.payload 
      }; // Menggabungkan data lama dengan yang baru
    default:
      return state;
  }
};

export const DeliveryProvider = ({ children }) => {
  const [state, dispatch] = useReducer(deliveryReducer, { data: null });

  return (
    <DeliveryContext.Provider value={{ 
      data: state.data, 
      setData: (payload) => dispatch({ type: 'SET_DATA', payload }) 
    }}>
      {children}
    </DeliveryContext.Provider>
  );
};

export const useDelivery = () => useContext(DeliveryContext);
