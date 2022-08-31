import AConnector from '@orchesty/nodejs-sdk/dist/lib/Connector/AConnector';
import logger from '@orchesty/nodejs-sdk/dist/lib/Logger/Logger';
import { HttpMethods } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';
import { BASE_URL } from './HubSpotApplication';

export default class HubSpotCreateContactConnector extends AConnector {

    public getName(): string {
        return 'hub-spot-create-contact';
    }

    public async processAction(dto: ProcessDto): Promise<ProcessDto> {
        const applicationInstall = await this.getApplicationInstallFromProcess(dto);

        const request = await this.getApplication().getRequestDto(
            dto,
            applicationInstall,
            HttpMethods.POST,
            `${BASE_URL}/crm/v3/objects/contacts`,
            dto.getData(),
        );

        const response = await this.getSender().send<IResponse>(request, [201, 409]);

        if (response.getResponseCode() === 409) {
            const email = dto.getJsonData();
            logger.error(`Contact "${email}" already exist.`, dto);
        }

        return dto.setData(response.getBody());
    }

}

interface IResponse {
    properties: {
        email: string;
    };
}
