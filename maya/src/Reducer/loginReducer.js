export const loginReducer = (prevState, action) => {
  switch (action.type) {
    case 'RETRIEVE_TOKEN':
      console.log('RETRIEVE_TOKEN action:', action);
      return {
        ...prevState, // Jangan hapus properti lama
        userData: action.userData,
        userToken: action.token,
        isLoading: false,
        organisation: action.organisation,
        fulfilment: action.fulfilment,
        warehouse: action.warehouse
      };
    case 'LOGIN':
      return {
        ...prevState,
        userData: action.userData,
        userToken: action.token,
        isLoading: false,
        organisation: action.organisation ?? null, // Pastikan tetap ada
        fulfilment: action.fulfilment ?? null,
        warehouse: action.warehouse ?? null
      };
    case 'LOGOUT':
      return {
        ...prevState,
        userData: null,
        userToken: null,
        isLoading: false,
        organisation: null,
        fulfilment: null,
        warehouse: null
      };
    case 'SET_ORGANISATION':
      return {
        ...prevState,
        organisation: action.organisation,
      };
    case 'SET_FULFILMENT_WAREHOUSE':
      return {
        ...prevState,
        fulfilment: action.fulfilment,
        warehouse: action.warehouse
      };
    default:
      return prevState;
  }
};
