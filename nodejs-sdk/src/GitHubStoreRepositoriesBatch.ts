import ABatchNode from '@orchesty/nodejs-sdk/dist/lib/Batch/ABatchNode';
import DataStorageManager from '@orchesty/nodejs-sdk/dist/lib/Storage/DataStore/DataStorageManager';
import { HttpMethods } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import BatchProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/BatchProcessDto';
import { PROCESS_ID } from '@orchesty/nodejs-sdk/dist/lib/Utils/Headers';
import GitHubApplication from './GitHubApplication';

export const NAME = 'git-hub-store-repositories-batch';
const PAGE_ITEMS = 5;

export default class GitHubStoreRepositoriesBatch extends ABatchNode {

    public constructor(private readonly dataStorageManager: DataStorageManager) {
        super();
    }

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
        const resp = await this.getSender().send<IResponse>(req, [200]);
        const response = resp.getJsonBody();

        const app = this.getApplication<GitHubApplication>();

        const processId = dto.getHeader(PROCESS_ID) ?? '';
        await this.dataStorageManager.store(
            processId,
            response,
            app.getName(),
            appInstall.getUser(),
        );

        if (response.length >= PAGE_ITEMS) {
            dto.setBatchCursor((Number(page) + 1).toString(), true);
        } else {
            dto.addItem({ processId });
        }

        return dto;
    }

}

type IResponse = IRepository[];

interface IRepository {
    repository: string;
}

export interface IInput {
    org: string;
}
