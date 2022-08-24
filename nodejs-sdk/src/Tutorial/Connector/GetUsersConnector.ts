import AConnector from '@orchesty/nodejs-sdk/dist/lib/Connector/AConnector';
import OnRepeatException from '@orchesty/nodejs-sdk/dist/lib/Exception/OnRepeatException';
import RequestDto from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/RequestDto';
import { HttpMethods } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';

export default class GetUsersConnector extends AConnector {

    public getName(): string {
        return 'get-users';
    }

    public async processAction(dto: ProcessDto): Promise<ProcessDto> {
        const request = new RequestDto(
            'https://jsonplaceholder.typicode.com/users',
            HttpMethods.GET,
            dto,
        );

        const response = await this.getSender().send(request);
        if (response.getResponseCode() >= 300) {
            throw new OnRepeatException(30, 5, response.getBody());
        }

        dto.setData(response.getBody());

        return dto;
    }

}
