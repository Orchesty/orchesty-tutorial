import AConnector from '@orchesty/nodejs-sdk/dist/lib/Connector/AConnector';
import OnRepeatException from '@orchesty/nodejs-sdk/dist/lib/Exception/OnRepeatException';
import { HttpMethods } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';
import ResultCode from '@orchesty/nodejs-sdk/dist/lib/Utils/ResultCode';

export default class GitHubGetRepositoryConnector extends AConnector {

    public getName(): string {
        return 'github-get-repository';
    }

    public async processAction(dto: ProcessDto<IInput>): Promise<ProcessDto> {
        const data = dto.getJsonData();
        const appInstall = await this.getApplicationInstallFromProcess(dto);

        if (!data.user || !data.repo) {
            dto.setStopProcess(ResultCode.STOP_AND_FAILED, 'Connector has no required data.');
        } else {
            const request = await this.getApplication().getRequestDto(dto, appInstall, HttpMethods.GET, `/repos/${data.user}/${data.repo}`);
            const response = await this.getSender().send(request);

            if (response.getResponseCode() >= 300 && response.getResponseCode() < 400) {
                throw new OnRepeatException(30, 5, response.getBody());
            } else if (response.getResponseCode() >= 400) {
                dto.setStopProcess(ResultCode.STOP_AND_FAILED, `Failed with code ${response.getResponseCode()}`);
            }

            dto.setData(response.getBody());
        }
        return dto;
    }

}

export interface IInput {
    user: string;
    repo: string;
}