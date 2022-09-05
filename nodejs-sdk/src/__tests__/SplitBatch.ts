import NodeTester from '@orchesty/nodejs-sdk/dist/test/Testers/NodeTester';
import { container } from '../../test/TestAbstract';
import { NAME as SPLIT_BATCH } from '../SplitBatch';

let tester: NodeTester;

describe('Tests for SplitBatch', () => {
    beforeAll(() => {
        tester = new NodeTester(container, __filename);
    });

    it('process - ok', async () => {
        await tester.testBatch(SPLIT_BATCH);
    });
});
