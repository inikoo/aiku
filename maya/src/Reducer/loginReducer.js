export const loginReducer = (prevState, action) => {
  switch (action.type) {
    case 'RETRIEVE_TOKEN':
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
        organisation: action.organisation, // Pastikan tetap ada
        fulfilment: action.fulfilment,
        warehouse: action.warehouse
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
        warehouse : null,
        fulfilment: null,
        warehouse: null
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
