// --- COMMONS ---
process.env.APP_ENV = 'debug'
process.env.CRYPT_SECRET = 'ThisIsNotSoSecret';
process.env.BACKEND_URL = 'http://127.0.0.42:8080'

if (process.env.JEST_DOCKER) {
  // --- DOCKER ---
  process.env.STARTING_POINT_URL = 'http://127.0.0.42:8080'
  process.env.WORKER_API_HOST = 'http://127.0.0.42:8080'
} else {
  // --- LOCALHOST ---
  process.env.STARTING_POINT_URL = 'http://127.0.0.42:3000'
  process.env.WORKER_API_HOST = 'http://127.0.0.42:8080'
}
