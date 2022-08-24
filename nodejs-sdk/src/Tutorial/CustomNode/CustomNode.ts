import ACommonNode from '@orchesty/nodejs-sdk/dist/lib/Commons/ACommonNode';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';

export class CustomNode extends ACommonNode {

    public getName(): string {
        return 'custom-node';
    }

    public processAction(dto: ProcessDto<IInput>): ProcessDto<IOutput> {
        const data = dto.getJsonData();

        // Whole data transformation
        return dto.setNewJsonData<IOutput>({
            key: data.key,
            personage: data.persons.reduce((acc: Record<string, IPersonage[]>, it) => {
                // Single person transformation
                const transformed = {
                    id: it.id,
                    fullname: `${it.name} ${it.surname}`,
                };

                if (!(it.assignment in acc)) {
                    acc[it.assignment] = [transformed];
                } else {
                    acc[it.assignment].push(transformed);
                }

                return acc;
            }, {}),
        });
    }

}

interface IInput {
    key: string;
    persons: {
        id: number;
        name: string;
        surname: string;
        assignment: string;
    }[];
}

// Exporting output type for any following node
export interface IOutput {
    key: string;
    personage: Record<string, IPersonage[]>;
}

export interface IPersonage {
    id: number;
    fullname: string;
}
