import AConnector from 'pipes-nodejs-sdk/dist/lib/Connector/AConnector';
import ProcessDto from 'pipes-nodejs-sdk/dist/lib/Utils/ProcessDto';
import HttpMethods from 'pipes-nodejs-sdk/dist/lib/Transport/HttpMethods';
import RequestDto from 'pipes-nodejs-sdk/dist/lib/Transport/Curl/RequestDto';
import OnRepeatException from 'pipes-nodejs-sdk/dist/lib/Exception/OnRepeatException';

export default class GetUsersConnector extends AConnector {

  public getName(): string {
    return 'get-users';
  }

  public async processAction(dto: ProcessDto): Promise<ProcessDto> {
    const request = new RequestDto(
      'https://jsonplaceholder.typicode.com/users',
      HttpMethods.GET,
    );

    const response = await this._sender.send(request);
    if (response.responseCode >= 300) {
      throw new OnRepeatException(30, 5, response.body);
    }

    dto.data = response.body;

    return dto;
  }

}
