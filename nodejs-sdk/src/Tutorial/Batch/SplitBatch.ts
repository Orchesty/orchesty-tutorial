import AConnector from '@orchesty/nodejs-sdk/dist/lib/Connector/AConnector';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';

export default class SplitBatch extends AConnector {
  public getName(): string {
    return 'split-batch';
  }

  public processAction(_dto: ProcessDto): Promise<ProcessDto> | ProcessDto {
    const dto = _dto;
    dto.jsonData = [{ id: 1 }, { id: 2 }, { id: 3 }];

    return dto;
  }
}
