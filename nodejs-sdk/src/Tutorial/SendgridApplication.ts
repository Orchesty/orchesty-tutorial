import AProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/AProcessDto';
import Field from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/Field';
import FieldType from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/FieldType';
import Form from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/Form';
import FormStack from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/FormStack';
import RequestDto from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/RequestDto';
import { ABasicApplication, PASSWORD, USER }
  from '@orchesty/nodejs-sdk/dist/lib/Authorization/Type/Basic/ABasicApplication';
import { ApplicationInstall } from '@orchesty/nodejs-sdk/dist/lib/Application/Database/ApplicationInstall';
import { AUTHORIZATION_FORM } from '@orchesty/nodejs-sdk/dist/lib/Application/Base/AApplication';
import { encode } from '@orchesty/nodejs-sdk/dist/lib/Utils/Base64';
import { JSON_TYPE, CommonHeaders } from '@orchesty/nodejs-sdk/dist/lib/Utils/Headers';
import { parseHttpMethod } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';

export default class SendgridApplication extends ABasicApplication {
  public getDescription = () => 'SendgridApplication';

  public getName = () => 'sendgrid';

  public getPublicName = () => 'Sendgrid';

  public getRequestDto(
    dto: AProcessDto,
    applicationInstall: ApplicationInstall,
    method: string,
    url?: string,
    data?: string,
  ): RequestDto {
    if (!this.isAuthorized(applicationInstall)) {
      throw new Error('Missing authorization settings');
    }

    const settings = applicationInstall.getSettings();
    const token = encode(`${settings[AUTHORIZATION_FORM][USER]}:${settings[AUTHORIZATION_FORM][PASSWORD]}`);
    const headers = {
      [CommonHeaders.AUTHORIZATION]: `Basic ${token}`,
      [CommonHeaders.ACCEPT]: JSON_TYPE,
      [CommonHeaders.CONTENT_TYPE]: JSON_TYPE,
    };

    return new RequestDto(url || '', parseHttpMethod(method), dto, data, headers);
  }

  public getFormStack(): FormStack {
    const form = new Form(AUTHORIZATION_FORM, 'Authorization settings');
    form
      .addField(new Field(FieldType.TEXT, USER, 'Username', undefined, true))
      .addField(new Field(FieldType.PASSWORD, PASSWORD, 'Password', undefined, true));

    return new FormStack().addForm(form);
  }
}
