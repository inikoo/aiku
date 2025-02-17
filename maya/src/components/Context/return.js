import React, { createContext, useContext, useState } from 'react';

const ReturnContext = createContext();

export const ReturnProvider = ({ children }) => {
  const [data, setData] = useState(null);

  return (
    <ReturnContext.Provider value={{ data, setData }}>
      {children}
    </ReturnContext.Provider>
  );
};

export const useReturn = () => useContext(ReturnContext);
