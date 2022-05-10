import AConnector from 'pipes-nodejs-sdk/dist/lib/Connector/AConnector';
import ProcessDto from 'pipes-nodejs-sdk/dist/lib/Utils/ProcessDto';

export default class CursorBatch extends AConnector {
  public getName(): string {
    return 'cursor-batch';
  }

  public processAction(_dto: ProcessDto): Promise<ProcessDto> | ProcessDto {
    const dto = _dto;
    const key = dto.getBatchCursor('firstKey');
    const response = this._fetchNextPage(key);

    dto.jsonData = response.data;
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
