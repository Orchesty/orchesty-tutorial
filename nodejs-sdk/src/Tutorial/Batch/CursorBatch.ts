import AConnector from 'pipes-nodejs-sdk/dist/lib/Connector/AConnector';
import ProcessDto from 'pipes-nodejs-sdk/dist/lib/Utils/ProcessDto';

export class CursorBatch extends AConnector {

  public getName(): string {
    return 'split-batch';
  }

  public processAction(dto: ProcessDto): Promise<ProcessDto> | ProcessDto {
    const key = dto.getBatchCursor('firstKey');
    const response = this.fetchNextPage(key);

    dto.jsonData = response.data;
    if (response.nextKey !== null) {
      dto.setBatchCursor(response.nextKey);
    }

    return dto;
  }

  private fetchNextPage(key: string): IData {
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
