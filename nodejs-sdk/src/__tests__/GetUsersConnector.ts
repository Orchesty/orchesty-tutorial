import NodeTester from '@orchesty/nodejs-sdk/dist/test/Testers/NodeTester';
import { container } from '../../test/TestAbstract';
import { NAME as GET_USERS_CONNECTOR } from '../GetUsersConnector';

let tester: NodeTester;

describe('Tests for GetUsersConnector', () => {
    beforeAll(() => {
        tester = new NodeTester(container, __filename);
    });

    it('process - ok', async () => {
        await tester.testConnector(GET_USERS_CONNECTOR);
    });
});
