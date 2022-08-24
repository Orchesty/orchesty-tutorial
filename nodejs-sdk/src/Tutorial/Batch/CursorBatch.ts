import ABatchNode from '@orchesty/nodejs-sdk/dist/lib/Batch/ABatchNode';
import BatchProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/BatchProcessDto';

export default class CursorBatch extends ABatchNode {

    public getName(): string {
        return 'cursor-batch';
    }

    public processAction(dto: BatchProcessDto): BatchProcessDto {
        const key = dto.getBatchCursor('firstKey');
        const response = this.fetchNextPage(Number(key));

        dto.setItemList(response.data);
        if (response.nextKey !== null) {
            dto.setBatchCursor(response.nextKey.toString());
        }

        return dto;
    }

    private fetchNextPage(key: number): IData {
        return {
            nextKey: key + 1,
            data: [],
        };
    }

}

interface IData {
    nextKey: number | null;
    data: object[];
}
