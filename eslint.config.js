import globals from 'globals';
import pluginJs from '@eslint/js';
import astro from 'eslint-plugin-astro';

export default [
  {
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.node,
      },
      parserOptions: {
        ecmaVersion: 2021,
        sourceType: 'module',
      },
    },
  },
  pluginJs.configs.recommended,
  astro.configs.recommended,
  {
    files: ['*.astro'],
    languageOptions: {
      parser: 'astro-eslint-parser',
      parserOptions: {
        parser: '@typescript-eslint/parser',
        extraFileExtensions: ['.astro'],
      },
    },
    rules: {
      'no-undef': 'error',
      'no-unused-vars': 'warn',
      eqeqeq: ['error', 'always'],
      curly: ['error', 'all'],
      // Add or override any rules for Astro files here
    },
  },
];
