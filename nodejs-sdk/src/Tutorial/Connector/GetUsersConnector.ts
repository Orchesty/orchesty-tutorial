import AConnector from '@orchesty/nodejs-sdk/dist/lib/Connector/AConnector';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';
import HttpMethods from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import RequestDto from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/RequestDto';
import OnRepeatException from '@orchesty/nodejs-sdk/dist/lib/Exception/OnRepeatException';

export default class GetUsersConnector extends AConnector {
  public getName(): string {
    return 'get-users';
  }

  public async processAction(_dto: ProcessDto): Promise<ProcessDto> {
    const dto = _dto;
    const request = new RequestDto(
      'https://jsonplaceholder.typicode.com/users',
      HttpMethods.GET,
      dto,
    );

    const response = await this._sender.send(request);
    if (response.responseCode >= 300) {
      throw new OnRepeatException(30, 5, response.body);
    }

    dto.data = response.body;

    return dto;
  }
}
