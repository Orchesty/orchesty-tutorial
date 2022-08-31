import ABatchNode from '@orchesty/nodejs-sdk/dist/lib/Batch/ABatchNode';
import BatchProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/BatchProcessDto';

export default class SplitBatch extends ABatchNode {

    public getName(): string {
        return 'split-batch';
    }

    public processAction(dto: BatchProcessDto): BatchProcessDto {
        dto.setItemList([{ id: 1 }, { id: 2 }, { id: 3 }]);

        return dto;
    }

}
