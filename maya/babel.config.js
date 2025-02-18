module.exports = {
  presets: ["module:@react-native/babel-preset", "nativewind/babel"],
  plugins: [
    [
      "module-resolver",
      {
        root: ["./"],
        extensions: [".js", ".ts", ".tsx", ".jsx"],
        alias: {
          "@": "./",
          "tailwind.config": "./tailwind.config.js",
        },
      },
    ],
    [
      'module:react-native-dotenv',
      {
        moduleName: '@env',
        path: '.env',
        allowUndefined: false,
        safe: true,
      },
    ],
    "react-native-reanimated/plugin",
  ],
};
