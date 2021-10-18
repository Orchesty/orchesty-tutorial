import ACommonNode from 'pipes-nodejs-sdk/dist/lib/Commons/ACommonNode';
import ProcessDto from 'pipes-nodejs-sdk/dist/lib/Utils/ProcessDto';

export class CustomNode extends ACommonNode {

  public getName(): string {
    return 'custom-node';
  }

  public processAction(_dto: ProcessDto): Promise<ProcessDto> | ProcessDto {
    const dto = _dto;
    // Specify what is an input
    const data = dto.jsonData as IInput;

    // Whole data transformation
    dto.jsonData = {
      key: data.key,
      personage: data.persons.reduce((acc: IPersonage, it) => {
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
      // Specifying output type to avoid unexpected keys
    } as IOutput;

    return dto;
  }

}

interface IInput {
  key: string,
  persons: {
    id: number,
    name: string,
    surname: string,
    assignment: string,
  }[]
}

// Exporting output type for any following node
export interface IOutput {
  key: string,
  personage: IPersonage,
}

export type IPersonage = Record<string, {
  id: number,
  fullname: string,
}[]>
