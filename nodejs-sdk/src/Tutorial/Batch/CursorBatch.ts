import ABatchNode from '@orchesty/nodejs-sdk/dist/lib/Batch/ABatchNode';
import BatchProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/BatchProcessDto';

export default class CursorBatch extends ABatchNode {
  public getName(): string {
    return 'cursor-batch';
  }

  public processAction(_dto: BatchProcessDto): Promise<BatchProcessDto> | BatchProcessDto {
    const dto = _dto;
    const key = dto.getBatchCursor('firstKey');
    const response = this._fetchNextPage(key);

    dto.setItemList(response.data);
    if (response.nextKey !== null) {
      dto.setBatchCursor(response.nextKey);
    }

    return dto;
  }

  private _fetchNextPage(key: string): IData {
    return {
      nextKey: null,
      data: [],
    };
  }
}

interface IData {
  nextKey: string | null,
  data: object[],
}
