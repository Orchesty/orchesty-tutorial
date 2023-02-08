import DataStorageManager from "@orchesty/nodejs-sdk/dist/lib/Storage/DataStore/DataStorageManager";
import { createLoggerMockedServer, createMetricsMockedServer } from "@orchesty/nodejs-sdk/dist/test/MockServer";
import { prepare, container } from '../test/TestAbstract';

jest.setTimeout(10000);

beforeAll(async () => {
    await prepare();
    createMetricsMockedServer();
    createLoggerMockedServer();
})

beforeEach(async () => {
    const manager = container.get(DataStorageManager);
    await manager.remove('testProcessId');
})

afterAll(async () => {

})
