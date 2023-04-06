// --- COMMONS ---
process.env.APP_ENV = 'prod' // 'debug' <= use it if you want to see more logs
process.env.CRYPT_SECRET = 'ThisIsNotSoSecret';
process.env.ORCHESTY_API_KEY = 'ThisIsNotSoSecretApiKey';
process.env.BACKEND_URL = 'http://127.0.0.42:8080'
process.env.STARTING_POINT_URL = 'http://127.0.0.42:8080'
process.env.WORKER_API_HOST = 'http://127.0.0.42:8080'

if (process.env.JEST_DOCKER) {
  // --- DOCKER ---
} else {
  // --- LOCALHOST ---
}
