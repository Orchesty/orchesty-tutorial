import ACommonNode from '@orchesty/nodejs-sdk/dist/lib/Commons/ACommonNode';
import DataStorageManager from '@orchesty/nodejs-sdk/dist/lib/Storage/DataStore/DataStorageManager';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';

export const NAME = 'load-repositories';

export default class LoadRepositories extends ACommonNode {

    public constructor(private readonly dataStorageManager: DataStorageManager) {
        super();
    }

    public getName(): string {
        return NAME;
    }

    public async processAction(dto: ProcessDto<IInput>): Promise<ProcessDto> {
        const { collection } = dto.getJsonData();
        const repos = await this.dataStorageManager.load(collection);
        return dto.setJsonData(repos);
    }

}

export interface IInput {
    collection: string;
}
