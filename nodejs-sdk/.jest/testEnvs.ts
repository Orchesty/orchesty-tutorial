import { readFileSync } from 'fs';
const devIp = readFileSync( __dirname + '/../.env')?.toString()?.match("(DEV_IP=)(.*)")?.[2] ?? '';
const devStartingPointUrl = `http://${devIp}:8080`;

// --- COMMONS ---
process.env.APP_ENV = 'prod' // 'debug' <= use it if you want to see more logs
process.env.CRYPT_SECRET = 'ThisIsNotSoSecret';
process.env.ORCHESTY_API_KEY = 'ThisIsNotSoSecretApiKey';
process.env.BACKEND_URL = `http://${devIp}:8080`;
process.env.STARTING_POINT_URL = devStartingPointUrl;
process.env.WORKER_API_HOST = `http://${devIp}:8080`;

export { devIp };