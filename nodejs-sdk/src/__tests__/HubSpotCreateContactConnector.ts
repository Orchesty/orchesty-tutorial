import NodeTester from '@orchesty/nodejs-sdk/dist/test/Testers/NodeTester';
import { hubspotAppInstall } from '../../test/DataProvider';
import { container } from '../../test/TestAbstract';
import { NAME as HUB_SPOT_CREATE_CONTACT_CONNECTOR } from '../HubSpotCreateContactConnector';

let tester: NodeTester;

describe('Tests for HubSpotCreateContactConnector', () => {
    beforeAll(() => {
        tester = new NodeTester(container, __filename);
        hubspotAppInstall();
    });

    it('process - ok', async () => {
        await tester.testConnector(HUB_SPOT_CREATE_CONTACT_CONNECTOR);
    });
});
