import { ApplicationInstall } from '@orchesty/nodejs-sdk/dist/lib/Application/Database/ApplicationInstall';
import DataStorageDocument from "@orchesty/nodejs-sdk/dist/lib/Storage/DataStore/Document/DataStorageDocument";
import { prepareApplications } from '../test/dataProvider';
import { dropCollection, prepare, closeConnection } from '../test/TestAbstract';

// Mock Logger module
jest.mock('@orchesty/nodejs-sdk/dist/lib/Logger/Logger', () => ({
    error: () => jest.fn(),
    info: () => jest.fn(),
    debug: () => jest.fn(),
    log: () => jest.fn(),
    ctxFromDto: () => jest.fn(),
    ctxFromReq: () => jest.fn(),
    Logger: jest.fn().mockImplementation(() => ({})),
}));

jest.setTimeout(10000);

beforeAll(async () => {
    await prepare();
})

beforeEach(async () => {
    await dropCollection(ApplicationInstall.getCollection());
    await dropCollection(DataStorageDocument.getCollection());
    await prepareApplications();
})

afterAll(async () => {
    await closeConnection();
})
