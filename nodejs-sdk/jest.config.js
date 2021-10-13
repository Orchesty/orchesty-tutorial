module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'node',
  testMatch: ['**/__tests__/*.ts'],
  setupFiles: ["<rootDir>/.jest/testEnvs.ts"],
  setupFilesAfterEnv: ["<rootDir>/.jest/testLifecycle.ts"],
};
