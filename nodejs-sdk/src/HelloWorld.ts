import ACommonNode from '@orchesty/nodejs-sdk/dist/lib/Commons/ACommonNode';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';

export const NAME = 'hello-world';

export default class HelloWorld extends ACommonNode {

    public getName(): string {
        return NAME;
    }

    public processAction(dto: ProcessDto): ProcessDto {
        return dto.setJsonData({ message: 'Hello world!' });
    }

}
