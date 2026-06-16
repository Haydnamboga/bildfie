// Shared flat ESLint config for the bildfie monorepo.
// Apps extend this and add framework-specific rules (Next.js, NestJS, RN).
const tseslint = require("@typescript-eslint/eslint-plugin");
const tsparser = require("@typescript-eslint/parser");
const prettier = require("eslint-config-prettier");

/** @type {import("eslint").Linter.Config[]} */
module.exports = [
  {
    files: ["**/*.{ts,tsx}"],
    languageOptions: {
      parser: tsparser,
      parserOptions: { ecmaVersion: "latest", sourceType: "module" },
    },
    plugins: { "@typescript-eslint": tseslint },
    rules: {
      "@typescript-eslint/no-unused-vars": [
        "warn",
        { argsIgnorePattern: "^_", varsIgnorePattern: "^_" },
      ],
      "@typescript-eslint/no-explicit-any": "warn",
    },
  },
  prettier,
  {
    ignores: ["**/dist/**", "**/.next/**", "**/node_modules/**", "**/generated/**"],
  },
];
