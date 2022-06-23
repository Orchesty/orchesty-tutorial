import { ApplicationInstall } from '@orchesty/nodejs-sdk/dist/lib/Application/Database/ApplicationInstall';
import { parseHttpMethod } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import RequestDto from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/RequestDto';
import Form from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/Form';
import Field from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/Field';
import FieldType from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/FieldType';
import { AUTHORIZATION_SETTINGS } from '@orchesty/nodejs-sdk/dist/lib/Application/Base/AApplication';
import {
  ABasicApplication,
  PASSWORD,
  USER,
} from '@orchesty/nodejs-sdk/dist/lib/Authorization/Type/Basic/ABasicApplication';
import { encode } from '@orchesty/nodejs-sdk/dist/lib/Utils/Base64';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';
import { JSON_TYPE, CommonHeaders } from '@orchesty/nodejs-sdk/dist/lib/Utils/Headers';

export default class SendgridApplication extends ABasicApplication {
  public getDescription = () => 'SendgridApplication';

  public getName = () => 'sendgrid';

  public getPublicName = () => 'Sendgrid';

  public getRequestDto(
    dto: ProcessDto,
    applicationInstall: ApplicationInstall,
    method: string,
    url?: string,
    data?: string,
  ): RequestDto {
    if (!this.isAuthorized(applicationInstall)) {
      throw new Error('Missing authorization settings');
    }

    const settings = applicationInstall.getSettings();
    const token = encode(
      `${settings[AUTHORIZATION_SETTINGS][USER]}:${settings[AUTHORIZATION_SETTINGS][PASSWORD]}`,
    );
    const headers = {
      [CommonHeaders.AUTHORIZATION]: `Basic ${token}`,
      [CommonHeaders.ACCEPT]: JSON_TYPE,
      [CommonHeaders.CONTENT_TYPE]: JSON_TYPE,
    };

    return new RequestDto(url || '', parseHttpMethod(method), dto, data, headers);
  }

  public getSettingsForm(): Form {
    const form = new Form();
    form
      .addField(new Field(FieldType.TEXT, 'username', 'Username', undefined, true))
      .addField(new Field(FieldType.PASSWORD, 'password', 'Password', undefined, true));

    return form;
  }
}
