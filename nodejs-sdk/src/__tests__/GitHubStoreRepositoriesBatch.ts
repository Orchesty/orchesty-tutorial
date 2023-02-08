import DataStorageManager from '@orchesty/nodejs-sdk/dist/lib/Storage/DataStore/DataStorageManager';
import NodeTester from '@orchesty/nodejs-sdk/dist/test/Testers/NodeTester';
import { githubAppInstall } from '../../test/DataProvider';
import { container } from '../../test/TestAbstract';
import { NAME as GIT_HUB_STORE_REPOSITORIES_BATCH } from '../GitHubStoreRepositoriesBatch';

let tester: NodeTester;

describe('Tests for GitHubStoreRepositoriesBatch', () => {
    beforeAll(() => {
        tester = new NodeTester(container, __filename);
        githubAppInstall();
    });

    it('process - ok', async () => {
        await tester.testBatch(GIT_HUB_STORE_REPOSITORIES_BATCH);
        const databaseClient = container.get(DataStorageManager);
        const process = await databaseClient.load('testProcessId');
        const data = process[0].getData();

        expect(process).toHaveLength(1);
        expect(data).toEqual({ repository: 'data' });
    });
});
