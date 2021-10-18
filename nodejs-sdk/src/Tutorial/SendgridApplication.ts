import { ApplicationInstall } from 'pipes-nodejs-sdk/dist/lib/Application/Database/ApplicationInstall';
import { parseHttpMethod } from 'pipes-nodejs-sdk/dist/lib/Transport/HttpMethods';
import RequestDto from 'pipes-nodejs-sdk/dist/lib/Transport/Curl/RequestDto';
import Form from 'pipes-nodejs-sdk/dist/lib/Application/Model/Form/Form';
import Field from 'pipes-nodejs-sdk/dist/lib/Application/Model/Form/Field';
import FieldType from 'pipes-nodejs-sdk/dist/lib/Application/Model/Form/FieldType';
import { AUTHORIZATION_SETTINGS } from 'pipes-nodejs-sdk/dist/lib/Application/Base/AApplication';
import {
  ABasicApplication,
  PASSWORD,
  USER,
} from 'pipes-nodejs-sdk/dist/lib/Authorization/Type/Basic/ABasicApplication';
import { encode } from 'pipes-nodejs-sdk/dist/lib/Utils/Base64';
import ProcessDto from 'pipes-nodejs-sdk/dist/lib/Utils/ProcessDto';

export default class SendgridApplication extends ABasicApplication {
  public getDescription = () => 'SendgridApplication';
  public getName = () => 'sendgrid';
  public getPublicName = () => 'Sendgrid';

  public getRequestDto(dto: ProcessDto, applicationInstall: ApplicationInstall, method: string, url?: string, data?: string): RequestDto {
    if (!this.isAuthorized(applicationInstall)) {
      throw new Error('Missing authorization settings');
    }

    const settings = applicationInstall.getSettings();
    const token = encode(
      `${settings[AUTHORIZATION_SETTINGS][USER]}:${settings[AUTHORIZATION_SETTINGS][PASSWORD]}`,
    );
    const headers = {
      Authorization: `Basic ${token}`,
      Accept: 'application/json',
      'Content-Type': 'application/json',
    };

    return new RequestDto(url || '', parseHttpMethod(method), data, headers, dto);
  }

  public getSettingsForm(): Form {
    const form = new Form();
    form
      .addField(new Field(FieldType.TEXT, 'username', 'Username', undefined, true))
      .addField(new Field(FieldType.PASSWORD, 'password', 'Password', undefined, true));

    return form;
  }

}
