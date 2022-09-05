import NodeTester from '@orchesty/nodejs-sdk/dist/test/Testers/NodeTester';
import { container } from '../../test/TestAbstract';
import { NAME as HELLO_WORLD } from '../HelloWorld';

let tester: NodeTester;

describe('Tests for HelloWorld', () => {
    beforeAll(() => {
        tester = new NodeTester(container, __filename);
    });

    it('process - ok', async () => {
        await tester.testCustomNode(HELLO_WORLD);
    });
});
