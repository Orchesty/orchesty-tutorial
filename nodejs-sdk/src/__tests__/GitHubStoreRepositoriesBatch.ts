import CoreServices from '@orchesty/nodejs-sdk/dist/lib/DIContainer/CoreServices';
import DataStorageDocument from '@orchesty/nodejs-sdk/dist/lib/Storage/DataStore/Document/DataStorageDocument';
import MongoDbClient from '@orchesty/nodejs-sdk/dist/lib/Storage/Mongodb/Client';
import NodeTester from '@orchesty/nodejs-sdk/dist/test/Testers/NodeTester';
import { container } from '../../test/TestAbstract';
import { NAME as GIT_HUB_STORE_REPOSITORIES_BATCH } from '../GitHubStoreRepositoriesBatch';

let tester: NodeTester;

describe('Tests for GitHubStoreRepositoriesBatch', () => {
    beforeAll(() => {
        tester = new NodeTester(container, __filename);
    });

    it('process - ok', async () => {
        await tester.testBatch(GIT_HUB_STORE_REPOSITORIES_BATCH);
        const mongo = container.get<MongoDbClient>(CoreServices.MONGO);
        const repo = await mongo.getRepository<DataStorageDocument>(DataStorageDocument);
        const data = await repo.findMany({ processId: 'testProcessId' });
        expect(data).toHaveLength(1);
        expect(data[0].getProcessId()).toBe('testProcessId');
        expect(data[0].getData()).toEqual({ repository: 'data' });
    });
});
