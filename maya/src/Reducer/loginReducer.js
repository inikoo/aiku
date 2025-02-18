export const loginReducer = (prevState, action) => {
  switch (action.type) {
    case 'RETRIEVE_TOKEN':
      return {
        userData: action.userData,
        userToken: action.token,
        isLoading: false,
        organisation: action.organisation,
        fulfilment: action.fulfilment,
        warehouse : action.warehouse
      };
    case 'LOGIN':
      return {
        userData: action.userData,
        userToken: action.token,
        isLoading: false,
        organisation: null,
        fulfilment: null,
        warehouse : null
      };
    case 'LOGOUT':
      return {
        userData: null,
        userToken: null,
        isLoading: false,
        warehouse : null
      };
    case 'SET_ORGANISATION':
      return {
        userData: action.userData,
        userToken: action.token,
        isLoading: false,
        organisation: action.organisation,
        fulfilment: null,
        warehouse : null
      };
    case 'SET_FULFILMENT_WAREHOUSE':
      return {
        userData: action.userData,
        userToken: action.token,
        isLoading: false,
        organisation: action.organisation,
        fulfilment: action.fulfilment,
        warehouse : action.warehouse
      };
    default:
      return prevState;
  }
};
