import AConnector from 'pipes-nodejs-sdk/dist/lib/Connector/AConnector';
import ProcessDto from 'pipes-nodejs-sdk/dist/lib/Utils/ProcessDto';

export class SplitBatch extends AConnector {

  public getName(): string {
    return 'split-batch';
  }

  public processAction(dto: ProcessDto): Promise<ProcessDto> | ProcessDto {
    dto.jsonData = [{id: 1}, {id: 2}, {id: 3}];

    return dto;
  }

}
