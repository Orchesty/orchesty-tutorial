import ABatchNode from '@orchesty/nodejs-sdk/dist/lib/Batch/ABatchNode';
import { HttpMethods } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import BatchProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/BatchProcessDto';

export const NAME = 'git-hub-repositories-batch';
const PAGE_ITEMS = 5;

export default class GitHubRepositoriesBatch extends ABatchNode {

    public getName(): string {
        return NAME;
    }

    public async processAction(dto: BatchProcessDto<IInput>): Promise<BatchProcessDto> {
        const page = dto.getBatchCursor('1');
        const { org } = dto.getJsonData();
        const appInstall = await this.getApplicationInstallFromProcess(dto);
        const req = await this.getApplication().getRequestDto(
            dto,
            appInstall,
            HttpMethods.GET,
            `/orgs/${org}/repos?per_page=${PAGE_ITEMS}&page=${page}`,
        );
        const resp = await this.getSender().send<unknown[]>(req, [200]);
        const response = resp.getJsonBody();

        dto.setItemList(response ?? []);
        if (response.length >= PAGE_ITEMS) {
            dto.setBatchCursor((Number(page) + 1).toString());
        }

        return dto;
    }

}

export interface IInput {
    org: string;
}
