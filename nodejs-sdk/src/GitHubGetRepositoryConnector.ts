import AConnector from '@orchesty/nodejs-sdk/dist/lib/Connector/AConnector';
import OnRepeatException from '@orchesty/nodejs-sdk/dist/lib/Exception/OnRepeatException';
import { HttpMethods } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';
import ResultCode from '@orchesty/nodejs-sdk/dist/lib/Utils/ResultCode';

export const NAME = 'github-get-repository';

export default class GitHubGetRepositoryConnector extends AConnector {

    public getName(): string {
        return NAME;
    }

    public async processAction(dto: ProcessDto<IInput>): Promise<ProcessDto> {
        const data = dto.getJsonData();
        const appInstall = await this.getApplicationInstallFromProcess(dto);

        if (!data.org || !data.repo) {
            dto.setStopProcess(ResultCode.STOP_AND_FAILED, 'Connector has no required data.');
        } else {
            const request = await this.getApplication().getRequestDto(dto, appInstall, HttpMethods.GET, `/repos/${data.org}/${data.repo}`);
            const response = await this.getSender().send(request, {
                success: '<400',
                stopAndFail: ['400-500'],
                repeat: '>=500',
            });

            dto.setData(response.getBody());
        }
        return dto;
    }

}

export interface IInput {
    org: string;
    repo: string;
}
