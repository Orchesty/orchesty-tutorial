import { createLoggerMockedServer, createMetricsMockedServer } from "@orchesty/nodejs-sdk/dist/test/MockServer";
import { prepare } from '../test/TestAbstract';

jest.setTimeout(10000);

beforeAll(async () => {
    await prepare();
    createMetricsMockedServer();
    createLoggerMockedServer();
})

beforeEach(async () => {
})

afterAll(async () => {

})
