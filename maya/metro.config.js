const { getDefaultConfig, mergeConfig } = require("@react-native/metro-config");
const { withNativeWind } = require("nativewind/metro");

const defaultConfig = getDefaultConfig(__dirname);

const config = mergeConfig(defaultConfig, {
  resolver: {
    extraNodeModules: {
      "react-dom": require.resolve("react-native"), // Aliaskan react-dom ke react-native
    },
  },
});

module.exports = withNativeWind(config, { input: "./global.css" });
