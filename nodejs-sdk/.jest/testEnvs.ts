// --- COMMONS ---
process.env.APP_ENV = 'debug'
process.env.CRYPT_SECRET = 'ThisIsNotSoSecret';
process.env.BACKEND_URL = 'http://127.0.0.42:8080'

if (process.env.JEST_DOCKER) {
  // --- DOCKER ---
  process.env.UDP_LOGGER_DSN = 'logstash:5005'
  process.env.METRICS_DSN = 'mongodb://mongo:27017/metrics'
  process.env.MONGODB_DSN = 'mongodb://mongo:27017/node-sdk'
} else {
  // --- LOCALHOST ---
  process.env.UDP_LOGGER_DSN = '127.0.0.42:5005'
  process.env.METRICS_DSN = 'mongodb://127.0.0.42:27017/metrics'
  process.env.MONGODB_DSN = 'mongodb://127.0.0.42:27017/node-sdk'
}

// Mock Logger module
jest.mock('pipes-nodejs-sdk/dist/lib/Logger/Logger', () => ({
  error: () => jest.fn(),
  info: () => jest.fn(),
  debug: () => jest.fn(),
  log: () => jest.fn(),
  ctxFromDto: () => jest.fn(),
  ctxFromReq: () => jest.fn(),
  // eslint-disable-next-line @typescript-eslint/naming-convention
  Logger: jest.fn().mockImplementation(() => ({})),
}));

jest.setTimeout(10000);
