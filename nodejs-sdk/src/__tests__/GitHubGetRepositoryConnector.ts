import NodeTester from '@orchesty/nodejs-sdk/dist/test/Testers/NodeTester';
import { githubAppInstall } from '../../test/DataProvider';
import { container } from '../../test/TestAbstract';
import { NAME as GIT_HUB_GET_REPOSITORY_CONNECTOR } from '../GitHubGetRepositoryConnector';

let tester: NodeTester;

describe('Tests for GitHubGetRepositoryConnector', () => {
    beforeAll(() => {
        tester = new NodeTester(container, __filename);
        githubAppInstall();
    });

    it('process - ok', async () => {
        await tester.testConnector(GIT_HUB_GET_REPOSITORY_CONNECTOR);
    });
});
