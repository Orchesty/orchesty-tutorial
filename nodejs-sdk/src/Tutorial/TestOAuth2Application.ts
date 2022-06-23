import AOAuth2Application from '@orchesty/nodejs-sdk/dist/lib/Authorization/Type/OAuth2/AOAuth2Application';
import ProcessDto from '@orchesty/nodejs-sdk/dist/lib/Utils/ProcessDto';
import { ApplicationInstall } from '@orchesty/nodejs-sdk/dist/lib/Application/Database/ApplicationInstall';
import HttpMethods from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import RequestDto from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/RequestDto';
import Form from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/Form';
import Field from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/Field';
import FieldType from '@orchesty/nodejs-sdk/dist/lib/Application/Model/Form/FieldType';
import { CLIENT_ID, CLIENT_SECRET } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Type/OAuth2/IOAuth2Application';
import ScopeSeparatorEnum from '@orchesty/nodejs-sdk/dist/lib/Authorization/ScopeSeparatorEnum';
import { ACCESS_TOKEN } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Provider/OAuth2/OAuth2Provider';
import { CommonHeaders, JSON_TYPE } from '@orchesty/nodejs-sdk/dist/lib/Utils/Headers';

export default class TestOAuth2Application extends AOAuth2Application {
  public getAuthUrl = (): string => 'https://identity.idoklad.cz/server/connect/authorize';

  public getTokenUrl = (): string => 'https://identity.idoklad.cz/server/connect/token';

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  public getScopes = (applicationInstall: ApplicationInstall): string[] => ['idoklad_api', 'offline_access'];

  protected _getScopesSeparator = (): string => ScopeSeparatorEnum.SPACE;

  public getDescription = (): string => 'Test OAuth2 application';

  public getName = (): string => 'oauth2application';

  public getPublicName = (): string => 'Test OAuth2 Application';

  public getRequestDto = (
    dto: ProcessDto,
    applicationInstall: ApplicationInstall,
    method: HttpMethods,
    url?: string,
    data?: string,
  ): RequestDto => new RequestDto(url ?? '', HttpMethods.GET, dto, data, {
    [CommonHeaders.AUTHORIZATION]: `Bearer ${this.getTokens(applicationInstall)[ACCESS_TOKEN]}`,
    [CommonHeaders.ACCEPT]: JSON_TYPE,
    [CommonHeaders.CONTENT_TYPE]: JSON_TYPE,
  });

  public getSettingsForm = (): Form => {
    const fieldClientId = new Field(FieldType.TEXT, CLIENT_ID, 'Client Id');
    const fieldClientSecret = new Field(FieldType.PASSWORD, CLIENT_SECRET, 'Client secret');

    const form = new Form();
    form
      .addField(fieldClientId)
      .addField(fieldClientSecret);

    return form;
  };
}
