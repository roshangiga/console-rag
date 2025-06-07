// eslint.config.js
import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import globals from "globals";

export default [
  // ESLint's recommended JavaScript rules
  js.configs.recommended,

  // Vue 3 recommended rules for flat config
  // See https://eslint.vuejs.org/user-guide/#configuration
  ...pluginVue.configs['flat/recommended'],

  {
    // Custom overrides and configurations
    languageOptions: {
      globals: {
        ...globals.browser,
      },
      parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module',
      }
    },
    settings: {
      vue: {
        version: "3"
      }
    },
    rules: {
      "vue/valid-v-slot": "off",
      "no-unused-vars": ["error", {
        "vars": "all",
        "args": "after-used",
        "ignoreRestSiblings": true,
        "varsIgnorePattern": "^_",
        "argsIgnorePattern": "^_",
        "caughtErrors": "all",
        "caughtErrorsIgnorePattern": "^_"
      }]
    }
  }
];
